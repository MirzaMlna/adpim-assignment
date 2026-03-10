<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class SppdDocxExporter
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const TEMPLATE_TYPE_SPPD = 'sppd';

    private const TEMPLATE_TYPE_SPT = 'spt';

    public function export(Assignment $assignment): string
    {
        $users = $assignment->assignmentUsers
            ->pluck('user')
            ->filter(fn ($user) => $user instanceof User)
            ->values();

        if ($users->isEmpty()) {
            throw new RuntimeException('Data petugas belum tersedia untuk assignment ini.');
        }

        $notaDinasBodyXml = $this->renderNotaDinasCoverBodyXml($assignment, $users);

        if ($assignment->region_classification === 'dalam_daerah_kabupaten') {
            return $this->exportDalamDaerahKabupatenDocument($assignment, $users, $notaDinasBodyXml);
        }

        [$templatePath, $templateType, $templateDisplayName] = $this->resolveTemplate($assignment);
        if (! File::exists($templatePath)) {
            throw new RuntimeException("Template {$templateDisplayName} tidak ditemukan di root project.");
        }

        $templateZip = new ZipArchive;
        if ($templateZip->open($templatePath) !== true) {
            throw new RuntimeException('Template SPPD tidak dapat dibuka.');
        }

        $documentXml = $templateZip->getFromName('word/document.xml');
        $numberingXml = $templateZip->getFromName('word/numbering.xml');
        $templateZip->close();

        if (! is_string($documentXml) || $documentXml === '') {
            throw new RuntimeException('Isi template SPPD tidak valid.');
        }

        [$mergedDocumentXml, $mergedNumberingXml] = $this->buildMergedDocumentXml(
            $documentXml,
            is_string($numberingXml) ? $numberingXml : null,
            $assignment,
            $users,
            $templateType,
            $notaDinasBodyXml
        );

        $tempDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tempDir);

        $safeCode = Str::slug((string) $assignment->code ?: 'assignment');
        $filePrefix = $templateType === self::TEMPLATE_TYPE_SPT ? 'spt' : 'sppd';
        $fileName = "{$filePrefix}-{$safeCode}-".now()->format('YmdHis').'.docx';
        $outputPath = $tempDir.DIRECTORY_SEPARATOR.$fileName;

        if (! copy($templatePath, $outputPath)) {
            throw new RuntimeException('Gagal menyalin template SPPD.');
        }

        $outputZip = new ZipArchive;
        if ($outputZip->open($outputPath) !== true) {
            throw new RuntimeException('File SPPD hasil tidak dapat dibuka.');
        }

        $outputZip->addFromString('word/document.xml', $mergedDocumentXml);
        if (is_string($mergedNumberingXml) && $mergedNumberingXml !== '') {
            $outputZip->addFromString('word/numbering.xml', $mergedNumberingXml);
        }
        $outputZip->close();

        return $outputPath;
    }

    private function exportDalamDaerahKabupatenDocument(
        Assignment $assignment,
        Collection $users,
        string $notaDinasBodyXml
    ): string {
        [$sptPath, $sptDisplayName] = $this->resolveSptHierarkiTemplate();
        [$sppdPath, $sppdDisplayName] = $this->resolveSppdTemplate();

        if (! File::exists($sptPath)) {
            throw new RuntimeException("Template {$sptDisplayName} tidak ditemukan di root project.");
        }

        if (! File::exists($sppdPath)) {
            throw new RuntimeException("Template {$sppdDisplayName} tidak ditemukan di root project.");
        }

        $sptZip = new ZipArchive;
        if ($sptZip->open($sptPath) !== true) {
            throw new RuntimeException("Template {$sptDisplayName} tidak dapat dibuka.");
        }

        $sptDocumentXml = $sptZip->getFromName('word/document.xml');
        $sptZip->close();

        if (! is_string($sptDocumentXml) || trim($sptDocumentXml) === '') {
            throw new RuntimeException("Isi template {$sptDisplayName} tidak valid.");
        }

        $sppdZip = new ZipArchive;
        if ($sppdZip->open($sppdPath) !== true) {
            throw new RuntimeException("Template {$sppdDisplayName} tidak dapat dibuka.");
        }

        $sppdDocumentXml = $sppdZip->getFromName('word/document.xml');
        $sppdNumberingXml = $sppdZip->getFromName('word/numbering.xml');
        $sppdZip->close();

        if (! is_string($sppdDocumentXml) || trim($sppdDocumentXml) === '') {
            throw new RuntimeException("Isi template {$sppdDisplayName} tidak valid.");
        }

        [$sptDocumentOpenTag, $sptTemplateBodyXml] = $this->extractTemplateParts($sptDocumentXml);
        [$sppdDocumentOpenTag, $sppdTemplateBodyXml, $sppdSectPrXml] = $this->extractTemplateParts($sppdDocumentXml);
        $numberingContext = $this->createNumberingContext(is_string($sppdNumberingXml) ? $sppdNumberingXml : null);

        $pages = [];
        if (trim($notaDinasBodyXml) !== '') {
            $pages[] = $notaDinasBodyXml;
        }

        $pages[] = $this->renderCombinedSptPage(
            $sptDocumentOpenTag,
            $sptTemplateBodyXml,
            $assignment,
            $users
        );

        $sheetNumber = 1;
        foreach ($users as $user) {
            $pageXml = $this->renderSinglePage(
                $sppdDocumentOpenTag,
                $sppdTemplateBodyXml,
                $assignment,
                $user,
                $sheetNumber
            );

            if ($numberingContext !== null) {
                $pageXml = $this->remapPageNumbering($pageXml, $numberingContext);
            }

            $pages[] = $pageXml;
            $sheetNumber++;
        }

        $bodyInnerXml = implode($this->pageBreakXml(), $pages).$sppdSectPrXml;
        $mergedDocumentXml = preg_replace(
            '/<w:body>.*<\/w:body>/su',
            '<w:body>'.$bodyInnerXml.'</w:body>',
            $sppdDocumentXml,
            1,
            $replaceCount
        );

        if (! is_string($mergedDocumentXml) || $replaceCount !== 1) {
            throw new RuntimeException('Konten dokumen SPT/SPPD gagal dibuat.');
        }

        $mergedNumberingXml = $numberingContext !== null
            ? ($numberingContext['dom']->saveXML() ?: $sppdNumberingXml)
            : $sppdNumberingXml;

        $tempDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tempDir);

        $safeCode = Str::slug((string) $assignment->code ?: 'assignment');
        $fileName = 'spt-sppd-'.$safeCode.'-'.now()->format('YmdHis').'.docx';
        $outputPath = $tempDir.DIRECTORY_SEPARATOR.$fileName;

        if (! copy($sppdPath, $outputPath)) {
            throw new RuntimeException('Gagal menyalin template SPPD.');
        }

        $outputZip = new ZipArchive;
        if ($outputZip->open($outputPath) !== true) {
            throw new RuntimeException('File SPT/SPPD hasil tidak dapat dibuka.');
        }

        $outputZip->addFromString('word/document.xml', $mergedDocumentXml);
        if (is_string($mergedNumberingXml) && $mergedNumberingXml !== '') {
            $outputZip->addFromString('word/numbering.xml', $mergedNumberingXml);
        }
        $outputZip->close();

        return $outputPath;
    }

    private function buildMergedDocumentXml(
        string $documentXml,
        ?string $numberingXml,
        Assignment $assignment,
        Collection $users,
        string $templateType,
        ?string $coverPageBodyXml = null
    ): array {
        [$documentOpenTag, $templateBodyXml, $sectPrXml] = $this->extractTemplateParts($documentXml);
        $numberingContext = $this->createNumberingContext($numberingXml);

        $pages = [];
        $sheetNumber = 1;

        if ($templateType === self::TEMPLATE_TYPE_SPT) {
            $pages[] = $this->renderCombinedSptPage(
                $documentOpenTag,
                $templateBodyXml,
                $assignment,
                $users
            );
        } else {
            foreach ($users as $user) {
                $pageXml = $this->renderSinglePage(
                    $documentOpenTag,
                    $templateBodyXml,
                    $assignment,
                    $user,
                    $sheetNumber
                );

                if ($numberingContext !== null) {
                    $pageXml = $this->remapPageNumbering($pageXml, $numberingContext);
                }

                $pages[] = $pageXml;
                $sheetNumber++;
            }
        }

        if (is_string($coverPageBodyXml) && trim($coverPageBodyXml) !== '') {
            array_unshift($pages, $coverPageBodyXml);
        }

        $bodyInnerXml = implode($this->pageBreakXml(), $pages).$sectPrXml;
        $updatedXml = preg_replace(
            '/<w:body>.*<\/w:body>/su',
            '<w:body>'.$bodyInnerXml.'</w:body>',
            $documentXml,
            1,
            $replaceCount
        );

        if (! is_string($updatedXml) || $replaceCount !== 1) {
            throw new RuntimeException('Konten SPPD gagal dibuat.');
        }

        $updatedNumberingXml = $numberingContext !== null
            ? ($numberingContext['dom']->saveXML() ?: $numberingXml)
            : $numberingXml;

        return [$updatedXml, $updatedNumberingXml];
    }

    private function extractTemplateParts(string $documentXml): array
    {
        if (! preg_match('/<w:document\b[^>]*>/u', $documentXml, $matches)) {
            throw new RuntimeException('Template SPPD tidak memiliki tag w:document yang valid.');
        }

        $documentOpenTag = $matches[0];

        $dom = new DOMDocument;
        if (! $dom->loadXML($documentXml)) {
            throw new RuntimeException('Template SPPD tidak dapat dibaca.');
        }

        $body = $dom->getElementsByTagNameNS(self::WORD_NS, 'body')->item(0);
        if (! $body) {
            throw new RuntimeException('Template SPPD tidak memiliki bagian body.');
        }

        $templateBodyXml = '';
        $sectPrXml = '';

        foreach ($body->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'sectPr') {
                $sectPrXml = $dom->saveXML($child);

                continue;
            }

            $templateBodyXml .= $dom->saveXML($child);
        }

        if ($templateBodyXml === '') {
            throw new RuntimeException('Template SPPD kosong.');
        }

        return [$documentOpenTag, $templateBodyXml, $sectPrXml];
    }

    private function renderSinglePage(
        string $documentOpenTag,
        string $templateBodyXml,
        Assignment $assignment,
        User $user,
        int $sheetNumber
    ): string {
        $pageXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .$documentOpenTag
            .'<w:body>'
            .$templateBodyXml
            .'</w:body></w:document>';

        $dom = new DOMDocument;
        if (! $dom->loadXML($pageXml)) {
            throw new RuntimeException('Gagal memuat halaman template SPPD.');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);
        $isOperationalOfficer = $this->isOperationalOfficer($user);

        $values = $this->buildReplacementValues($assignment, $user, $sheetNumber);
        $legacyQueue = [
            $values['user_assignment_regulation_level'],
            $values['assignment_transportation'],
            $values['assignment_boarding_date'],
            $values['assignment_return_date'],
        ];

        foreach ($xpath->query('//w:body//w:p') as $paragraph) {
            $paragraphText = $this->getParagraphText($xpath, $paragraph);
            if ($this->shouldHideIdentityLineForOperationalOfficer($paragraphText, $isOperationalOfficer)) {
                if ($paragraph->parentNode) {
                    $paragraph->parentNode->removeChild($paragraph);
                }

                continue;
            }

            $this->replaceParagraphPlaceholders($xpath, $paragraph, $values, $legacyQueue);
        }

        $body = $dom->getElementsByTagNameNS(self::WORD_NS, 'body')->item(0);
        if (! $body) {
            throw new RuntimeException('Gagal membaca body halaman SPPD.');
        }

        $innerXml = '';
        foreach ($body->childNodes as $child) {
            $innerXml .= $dom->saveXML($child);
        }

        return $innerXml;
    }

    private function renderCombinedSptPage(
        string $documentOpenTag,
        string $templateBodyXml,
        Assignment $assignment,
        Collection $users
    ): string {
        $pageXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .$documentOpenTag
            .'<w:body>'
            .$templateBodyXml
            .'</w:body></w:document>';

        $dom = new DOMDocument;
        if (! $dom->loadXML($pageXml)) {
            throw new RuntimeException('Gagal memuat halaman template SPT.');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $this->injectSptRecipients($xpath, $assignment, $users);

        $values = $this->buildReplacementValues($assignment, null, 1);
        $legacyQueue = [
            $values['user_assignment_regulation_level'],
            $values['assignment_transportation'],
            $values['assignment_boarding_date'],
            $values['assignment_return_date'],
        ];

        foreach ($xpath->query('//w:body//w:p') as $paragraph) {
            $this->replaceParagraphPlaceholders($xpath, $paragraph, $values, $legacyQueue);
        }

        $body = $dom->getElementsByTagNameNS(self::WORD_NS, 'body')->item(0);
        if (! $body) {
            throw new RuntimeException('Gagal membaca body halaman SPT.');
        }

        $innerXml = '';
        foreach ($body->childNodes as $child) {
            $innerXml .= $dom->saveXML($child);
        }

        return $innerXml;
    }

    private function replaceKnownPlaceholders(string $text, array $values): string
    {
        $patterns = [
            // Handle custom formula in template.
            // Specific case for signature block: assignment date minus one day.
            '/\{assignments\s*[-\x{2013}\?]\s*date\}\s*\+\s*\{assignments\s*[-\x{2013}\?]\s*day_count\}\s*[-\x{2013}\x{2212}]\s*1/u' => $values['assignment_issue_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*date\}\s*\+\s*\{assignments\s*[-\x{2013}\?]\s*day_count\}/u' => $values['assignment_return_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*boarding_date\}\s*\+\s*\{assignments\s*[-\x{2013}\?]\s*day_count\}\s*[-\x{2013}\x{2212}]\s*1/u' => $values['assignment_return_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*boarding_date\}\s*\+\s*\{assignments\s*[-\x{2013}\?]\s*day_count\}/u' => $values['assignment_return_date'],
            '/\{users\s*[-\x{2013}\?]\s*name\}/u' => $values['user_name'],
            '/\{users\s*[-\x{2013}\?]\s*nip\}/u' => $values['user_nip'],
            '/\{users\s*[-\x{2013}\?]\s*rank\}/u' => $values['user_rank'],
            '/\{users\s*[-\x{2013}\?]\s*job_title\}/u' => $values['user_job_title'],
            '/\{attendeds\s*[-\x{2013}\?]\s*rank[^}]*\}/u' => $values['attendeds_rank_kalsel'],
            '/\{users\s*[-\x{2013}\?]\s*assignment_regulation_level\}/u' => $values['user_assignment_regulation_level'],
            '/\{assignments\s*[-\x{2013}\?]\s*title\}/u' => $values['assignment_title'],
            '/\{assignments\s*[-\x{2013}\?]\s*date\}/u' => $values['assignment_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*location_detail\}/u' => $values['assignment_location_detail'],
            '/\{assignments\s*[-\x{2013}\?]\s*location\}/u' => $values['assignment_location'],
            '/\{assignments\s*[-\x{2013}\?]\s*day_count\}/u' => $values['assignment_day_count'],
            '/\{assignments\s*[-\x{2013}\?]\s*transportation\}/u' => $values['assignment_transportation'],
            '/\{assignments\s*[-\x{2013}\?]\s*boarding_date\}/u' => $values['assignment_boarding_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*return_date\}/u' => $values['assignment_return_date'],
            '/\{assignments\s*[-\x{2013}\?]\s*description\}/u' => $values['assignment_description'],
            '/\{assignments\s*[-\x{2013}\?]\s*date\s*\(\s*dikurangi\s*(?:satu|1)\s*hari\s*\)\}/u' => $values['assignment_issue_date'],
            '/\{sheet_number\}/u' => $values['sheet_number'],
            '/___000\.1\.2\.3/u' => $values['assignment_code'],
            '/________\/ADPIM\/2025/u' => $values['assignment_number'],
        ];

        $result = $text;
        foreach ($patterns as $pattern => $replacement) {
            $safeReplacement = str_replace(['\\', '$'], ['\\\\', '\\$'], (string) $replacement);
            $result = preg_replace($pattern, $safeReplacement, $result) ?? $result;
        }

        return $result;
    }

    private function replaceLegacyPlaceholders(string $text, array &$legacyQueue): string
    {
        $result = preg_replace_callback(
            '/Belum ada kolom pada database/u',
            static function () use (&$legacyQueue): string {
                $value = array_shift($legacyQueue);
                $value = trim((string) $value);

                return $value !== '' ? $value : '-';
            },
            $text
        );

        return $result ?? $text;
    }

    private function replaceParagraphPlaceholders(
        DOMXPath $xpath,
        mixed $paragraph,
        array $values,
        array &$legacyQueue
    ): void {
        $tabNodes = $xpath->query('.//w:tab', $paragraph);
        if ($tabNodes && $tabNodes->length > 0) {
            $this->replaceParagraphPlaceholdersNodeWise($xpath, $paragraph, $values, $legacyQueue);

            return;
        }

        $textNodes = $xpath->query('.//w:t', $paragraph);
        if (! $textNodes || $textNodes->length === 0) {
            return;
        }

        $originalText = '';
        foreach ($textNodes as $textNode) {
            $originalText .= $textNode->textContent;
        }

        $updatedText = $this->replaceKnownPlaceholders($originalText, $values);
        $updatedText = $this->replaceLegacyPlaceholders($updatedText, $legacyQueue);

        if ($updatedText === $originalText) {
            return;
        }

        $textNodes->item(0)->textContent = $updatedText;
        for ($i = 1; $i < $textNodes->length; $i++) {
            $textNodes->item($i)->nodeValue = '';
        }
    }

    private function replaceParagraphPlaceholdersNodeWise(
        DOMXPath $xpath,
        mixed $paragraph,
        array $values,
        array &$legacyQueue
    ): void {
        $textNodes = $xpath->query('.//w:t', $paragraph);
        if (! $textNodes || $textNodes->length === 0) {
            return;
        }

        $originalNodeTexts = [];
        $originalText = '';
        foreach ($textNodes as $textNode) {
            $nodeText = (string) $textNode->textContent;
            $originalNodeTexts[] = $nodeText;
            $originalText .= $nodeText;
        }

        $updatedText = $this->replaceKnownPlaceholders($originalText, $values);
        $updatedText = $this->replaceLegacyPlaceholders($updatedText, $legacyQueue);

        if ($updatedText === $originalText) {
            return;
        }

        $charOffset = 0;
        $lastIndex = count($originalNodeTexts) - 1;
        foreach ($textNodes as $index => $textNode) {
            if ($index === $lastIndex) {
                $textNode->textContent = $this->substringText($updatedText, $charOffset);

                continue;
            }

            $chunkLength = $this->textLength($originalNodeTexts[$index] ?? '');
            $chunk = $this->substringText($updatedText, $charOffset, $chunkLength);
            $textNode->textContent = $chunk;
            $charOffset += $this->textLength($chunk);
        }
    }

    private function textLength(string $text): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($text, 'UTF-8')
            : strlen($text);
    }

    private function substringText(string $text, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return $length === null
                ? (string) mb_substr($text, $start, null, 'UTF-8')
                : (string) mb_substr($text, $start, $length, 'UTF-8');
        }

        return $length === null
            ? substr($text, $start)
            : substr($text, $start, $length);
    }

    private function buildReplacementValues(Assignment $assignment, ?User $user, int $sheetNumber): array
    {
        $assignmentDate = $assignment->date instanceof Carbon
            ? $assignment->date->copy()
            : Carbon::parse($assignment->date);
        $dayCount = max(1, (int) $assignment->day_count);
        $boardingDate = $assignment->boarding_date
            ? ($assignment->boarding_date instanceof Carbon
                ? $assignment->boarding_date->copy()
                : Carbon::parse($assignment->boarding_date))
            : null;

        $returnDateForSppd = $boardingDate
            ? $boardingDate->copy()->addDays(max(0, $dayCount - 1))
            : null;
        $issueDate = $assignmentDate->copy()->subDay();
        $attendedsRankKalsel = $this->buildAttendedsRankKalselText($assignment);
        $userRankForJabatan = $this->valueOrDash($user?->rank, $user?->job_title);
        $userJobTitleForPangkat = $this->valueOrDash($user?->job_title, $user?->rank);

        return [
            'user_name' => $this->valueOrDash($user?->name),
            'user_nip' => $this->valueOrDash($user?->nip),
            'user_rank' => $userJobTitleForPangkat,
            'user_job_title' => $userRankForJabatan,
            'user_assignment_regulation_level' => $this->valueOrDash($user?->assignment_regulation_level),
            'assignment_code' => $this->valueOrDash($assignment->code),
            'assignment_number' => $this->valueOrDash($assignment->code),
            'assignment_title' => $this->valueOrDash($assignment->title),
            'assignment_date' => $this->formatIndonesianDate($assignmentDate),
            'assignment_issue_date' => $this->formatIndonesianDate($issueDate),
            'assignment_location_detail' => $this->valueOrDash($assignment->location_detail, $assignment->location),
            'assignment_location' => $this->valueOrDash($assignment->location),
            'assignment_day_count' => $this->formatDayCount($dayCount),
            'assignment_boarding_date' => $this->formatIndonesianDate($boardingDate ?? $assignment->boarding_date),
            'assignment_return_date' => $this->formatIndonesianDate($returnDateForSppd),
            'assignment_transportation' => $this->valueOrDash($assignment->transportation),
            'assignment_description' => $this->valueOrDash($assignment->description),
            'attendeds_rank_kalsel' => $attendedsRankKalsel,
            'sheet_number' => (string) $sheetNumber,
        ];
    }

    private function injectSptRecipients(DOMXPath $xpath, Assignment $assignment, Collection $users): void
    {
        $paragraphNodes = $xpath->query('//w:body/w:p');
        if (! $paragraphNodes || $paragraphNodes->length === 0) {
            return;
        }

        $paragraphs = [];
        foreach ($paragraphNodes as $index => $paragraphNode) {
            $text = $this->getParagraphText($xpath, $paragraphNode);
            $normalizedText = $this->normalizeTextForComparison($text);

            $paragraphs[$index] = [
                'index' => $index,
                'node' => $paragraphNode,
                'text' => $text,
                'normalized_text' => $normalizedText,
                'is_empty' => trim($text) === '',
            ];
        }

        $kepadaIndex = null;
        $untukIndex = null;
        foreach ($paragraphs as $paragraph) {
            $compact = str_replace(' ', '', $paragraph['normalized_text']);
            if ($kepadaIndex === null && str_starts_with($compact, 'kepada')) {
                $kepadaIndex = $paragraph['index'];
            }

            if ($untukIndex === null && str_starts_with($compact, 'untuk')) {
                $untukIndex = $paragraph['index'];
            }
        }

        if (! is_int($kepadaIndex) || ! is_int($untukIndex) || $untukIndex <= $kepadaIndex) {
            return;
        }

        $recipientStart = $kepadaIndex + 1;
        $recipientEnd = $untukIndex - 1;
        if ($recipientStart > $recipientEnd) {
            return;
        }

        $recipientParagraphs = [];
        for ($idx = $recipientStart; $idx <= $recipientEnd; $idx++) {
            $paragraph = $paragraphs[$idx] ?? null;
            if (! $paragraph) {
                continue;
            }

            $recipientParagraphs[] = $paragraph;
        }

        if ($recipientParagraphs === []) {
            return;
        }

        $nameLocalIndices = [];
        $conditionLocalIndices = [];
        $blankLocalIndex = null;
        foreach ($recipientParagraphs as $localIndex => $paragraph) {
            if ((bool) preg_match('/\{users\s*[-\x{2013}\?]\s*name\}/u', $paragraph['text'])) {
                $nameLocalIndices[] = $localIndex;
            }

            if ($this->isSptConditionNoteParagraph($xpath, $paragraph['node'], $paragraph['normalized_text'])) {
                $conditionLocalIndices[] = $localIndex;
            }

            if ($blankLocalIndex === null && $paragraph['is_empty']) {
                $blankLocalIndex = $localIndex;
            }
        }

        if (count($nameLocalIndices) < 2) {
            return;
        }

        sort($nameLocalIndices);
        sort($conditionLocalIndices);

        $firstNameLocal = $nameLocalIndices[0];
        $secondNameLocal = $nameLocalIndices[1];
        $firstConditionLocal = $conditionLocalIndices[0] ?? null;

        $firstBlockNodes = [];
        for ($idx = $firstNameLocal; $idx <= max($firstNameLocal, $secondNameLocal - 1); $idx++) {
            if (! isset($recipientParagraphs[$idx])) {
                continue;
            }

            $firstBlockNodes[] = $recipientParagraphs[$idx]['node']->cloneNode(true);
        }

        $secondBlockEnd = is_int($firstConditionLocal) && $firstConditionLocal > $secondNameLocal
            ? $firstConditionLocal - 1
            : count($recipientParagraphs) - 1;

        $secondBlockNodes = [];
        for ($idx = $secondNameLocal; $idx <= $secondBlockEnd; $idx++) {
            if (! isset($recipientParagraphs[$idx])) {
                continue;
            }

            if ($this->isSptConditionNoteParagraph(
                $xpath,
                $recipientParagraphs[$idx]['node'],
                $recipientParagraphs[$idx]['normalized_text']
            )) {
                continue;
            }

            $secondBlockNodes[] = $recipientParagraphs[$idx]['node']->cloneNode(true);
        }

        $separatorNode = null;
        if (is_int($blankLocalIndex) && isset($recipientParagraphs[$blankLocalIndex])) {
            $separatorNode = $recipientParagraphs[$blankLocalIndex]['node']->cloneNode(true);
        }

        $insertBeforeNode = $paragraphs[$untukIndex]['node'] ?? null;
        if (! $insertBeforeNode || ! $insertBeforeNode->parentNode) {
            return;
        }

        $parentNode = $insertBeforeNode->parentNode;

        for ($idx = $recipientEnd; $idx >= $recipientStart; $idx--) {
            $node = $paragraphs[$idx]['node'] ?? null;
            if ($node && $node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }

        foreach ($users as $userIndex => $user) {
            if (! $user instanceof User) {
                continue;
            }

            $isOperationalOfficer = $this->isOperationalOfficer($user);

            $templateNodes = $isOperationalOfficer && $secondBlockNodes !== []
                ? $secondBlockNodes
                : $firstBlockNodes;

            if ($templateNodes === []) {
                $templateNodes = $secondBlockNodes;
            }

            $values = $this->buildReplacementValues($assignment, $user, $userIndex + 1);
            $legacyQueue = [];

            foreach ($templateNodes as $templateNode) {
                $node = $templateNode->cloneNode(true);
                $nodeText = $this->getParagraphText($xpath, $node);
                if ($this->shouldHideIdentityLineForOperationalOfficer($nodeText, $isOperationalOfficer)) {
                    continue;
                }

                $this->replaceParagraphPlaceholders($xpath, $node, $values, $legacyQueue);
                $parentNode->insertBefore($node, $insertBeforeNode);
            }

            if ($userIndex < ($users->count() - 1) && $separatorNode) {
                $parentNode->insertBefore($separatorNode->cloneNode(true), $insertBeforeNode);
            }
        }
    }

    private function isSptConditionNoteParagraph(DOMXPath $xpath, mixed $paragraphNode, string $normalizedText): bool
    {
        if ($normalizedText === '') {
            return false;
        }

        $compactText = str_replace(' ', '', $normalizedText);
        $containsConditionWords = str_contains($compactText, 'nomor')
            && str_contains($compactText, 'contoh')
            && str_contains($compactText, 'jasatenagaadministrasi');

        if (! $containsConditionWords) {
            return false;
        }

        $hasItalic = $xpath->query('./w:pPr/w:rPr/w:i | .//w:rPr/w:i', $paragraphNode);

        return $hasItalic !== false && $hasItalic !== null && $hasItalic->length > 0;
    }

    private function getParagraphText(DOMXPath $xpath, mixed $paragraphNode): string
    {
        $text = '';
        $textNodes = $xpath->query('.//w:t', $paragraphNode);
        if (! $textNodes) {
            return $text;
        }

        foreach ($textNodes as $textNode) {
            $text .= $textNode->textContent;
        }

        return $text;
    }

    private function normalizeTextForComparison(string $text): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

        return function_exists('mb_strtolower')
            ? mb_strtolower($normalized, 'UTF-8')
            : strtolower($normalized);
    }

    private function isOperationalOfficerJobTitle(string $jobTitle): bool
    {
        return $this->normalizeTextForComparison($jobTitle)
            === $this->normalizeTextForComparison('Jasa Tenaga Administrasi');
    }

    private function isOperationalOfficer(User $user): bool
    {
        return $this->isOperationalOfficerJobTitle((string) $user->job_title)
            || $this->isOperationalOfficerJobTitle((string) $user->rank);
    }

    private function shouldHideIdentityLineForOperationalOfficer(string $paragraphText, bool $isOperationalOfficer): bool
    {
        if (! $isOperationalOfficer) {
            return false;
        }

        $hasUserNipPlaceholder = preg_match('/\{users\s*[-\x{2013}\?]\s*nip\}/u', $paragraphText) === 1;
        $hasUserRankPlaceholder = preg_match('/\{users\s*[-\x{2013}\?]\s*rank\}/u', $paragraphText) === 1;

        return $hasUserNipPlaceholder || $hasUserRankPlaceholder;
    }

    private function resolveTemplate(Assignment $assignment): array
    {
        if ($assignment->region_classification === 'dalam_daerah') {
            [$path, $label] = $this->resolveSptTemplate();

            return [$path, self::TEMPLATE_TYPE_SPT, $label];
        }

        [$path, $label] = $this->resolveSppdTemplate();

        return [$path, self::TEMPLATE_TYPE_SPPD, $label];
    }

    private function resolveSptTemplate(): array
    {
        $candidates = [
            ['file' => 'LEMBAR_SPT.docx', 'label' => 'LEMBAR_SPT.docx'],
            ['file' => 'LEMBAR SPT.docx', 'label' => 'LEMBAR SPT.docx'],
        ];

        foreach ($candidates as $candidate) {
            $path = base_path($candidate['file']);
            if (File::exists($path)) {
                return [$path, $candidate['label']];
            }
        }

        return [base_path('LEMBAR_SPT.docx'), 'LEMBAR_SPT.docx'];
    }

    private function resolveSppdTemplate(): array
    {
        $candidates = [
            ['file' => 'LEMBAR_SPPD.docx', 'label' => 'LEMBAR_SPPD.docx'],
            ['file' => 'LEMBAR SPPD.docx', 'label' => 'LEMBAR SPPD.docx'],
        ];

        foreach ($candidates as $candidate) {
            $path = base_path($candidate['file']);
            if (File::exists($path)) {
                return [$path, $candidate['label']];
            }
        }

        return [base_path('LEMBAR_SPPD.docx'), 'LEMBAR_SPPD.docx'];
    }

    private function resolveSptHierarkiTemplate(): array
    {
        $candidates = [
            ['file' => 'LEMBAR_SPT_HIERARKI.docx', 'label' => 'LEMBAR_SPT_HIERARKI.docx'],
            ['file' => 'LEMBAR SPT HIERARKI.docx', 'label' => 'LEMBAR SPT HIERARKI.docx'],
        ];

        foreach ($candidates as $candidate) {
            $path = base_path($candidate['file']);
            if (File::exists($path)) {
                return [$path, $candidate['label']];
            }
        }

        return [base_path('LEMBAR_SPT_HIERARKI.docx'), 'LEMBAR_SPT_HIERARKI.docx'];
    }

    private function renderNotaDinasCoverBodyXml(Assignment $assignment, Collection $users): string
    {
        [$notaPath, $notaDisplayName] = $this->resolveNotaDinasTemplate();
        if (! File::exists($notaPath)) {
            throw new RuntimeException("Template {$notaDisplayName} tidak ditemukan di root project.");
        }

        $notaZip = new ZipArchive;
        if ($notaZip->open($notaPath) !== true) {
            throw new RuntimeException("Template {$notaDisplayName} tidak dapat dibuka.");
        }

        $notaDocumentXml = $notaZip->getFromName('word/document.xml');
        $notaZip->close();

        if (! is_string($notaDocumentXml) || trim($notaDocumentXml) === '') {
            throw new RuntimeException("Isi template {$notaDisplayName} tidak valid.");
        }

        [$documentOpenTag, $notaBodyXml] = $this->extractTemplateParts($notaDocumentXml);
        $pageXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .$documentOpenTag
            .'<w:body>'
            .$notaBodyXml
            .'</w:body></w:document>';

        $dom = new DOMDocument;
        if (! $dom->loadXML($pageXml)) {
            throw new RuntimeException("Template {$notaDisplayName} tidak dapat dibaca.");
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $this->injectNotaDinasUserRows($xpath, $assignment, $users);

        $values = $this->buildCoverReplacementValues($assignment, $users);
        $legacyQueue = [
            $values['user_assignment_regulation_level'],
            $values['assignment_transportation'],
            $values['assignment_boarding_date'],
            $values['assignment_return_date'],
        ];

        foreach ($xpath->query('//w:body//w:p') as $paragraph) {
            $this->replaceParagraphPlaceholders($xpath, $paragraph, $values, $legacyQueue);
        }

        $body = $dom->getElementsByTagNameNS(self::WORD_NS, 'body')->item(0);
        if (! $body) {
            throw new RuntimeException("Gagal membaca isi body {$notaDisplayName}.");
        }

        $innerXml = '';
        foreach ($body->childNodes as $child) {
            $innerXml .= $dom->saveXML($child);
        }

        return $innerXml;
    }

    private function resolveNotaDinasTemplate(): array
    {
        $candidates = [
            ['file' => 'LEMBAR_NOTADINAS.docx', 'label' => 'LEMBAR_NOTADINAS.docx'],
            ['file' => 'LEMBAR NOTADINAS.docx', 'label' => 'LEMBAR NOTADINAS.docx'],
        ];

        foreach ($candidates as $candidate) {
            $path = base_path($candidate['file']);
            if (File::exists($path)) {
                return [$path, $candidate['label']];
            }
        }

        return [base_path('LEMBAR_NOTADINAS.docx'), 'LEMBAR_NOTADINAS.docx'];
    }

    private function buildCoverReplacementValues(Assignment $assignment, Collection $users): array
    {
        $firstUser = $users->first();
        $firstUser = $firstUser instanceof User ? $firstUser : null;

        return $this->buildReplacementValues($assignment, $firstUser, 1);
    }

    private function injectNotaDinasUserRows(DOMXPath $xpath, Assignment $assignment, Collection $users): void
    {
        $rows = $xpath->query('//w:body//w:tbl//w:tr');
        if (! $rows || $rows->length === 0) {
            return;
        }

        $rowsToExpand = [];
        foreach ($rows as $rowNode) {
            if ($this->hasNotaDinasUserPlaceholders($xpath, $rowNode)) {
                $rowsToExpand[] = $rowNode;
            }
        }

        if ($rowsToExpand === []) {
            return;
        }

        $validUsers = $users
            ->filter(fn ($user) => $user instanceof User)
            ->values();

        if ($validUsers->isEmpty()) {
            return;
        }

        foreach ($rowsToExpand as $templateRowNode) {
            $parentNode = $templateRowNode->parentNode;
            if (! $parentNode) {
                continue;
            }

            foreach ($validUsers as $index => $user) {
                if (! $user instanceof User) {
                    continue;
                }

                $rowNode = $templateRowNode->cloneNode(true);
                $parentNode->insertBefore($rowNode, $templateRowNode);

                $values = $this->buildReplacementValues($assignment, $user, $index + 1);
                $legacyQueue = [];
                foreach ($xpath->query('.//w:p', $rowNode) as $paragraphNode) {
                    $this->replaceParagraphPlaceholders($xpath, $paragraphNode, $values, $legacyQueue);
                }

                $this->setNotaDinasUserRowNumber($xpath, $rowNode, $index + 1);
            }

            $parentNode->removeChild($templateRowNode);
        }
    }

    private function hasNotaDinasUserPlaceholders(DOMXPath $xpath, mixed $rowNode): bool
    {
        $textNodes = $xpath->query('.//w:t', $rowNode);
        if (! $textNodes || $textNodes->length === 0) {
            return false;
        }

        foreach ($textNodes as $textNode) {
            if (preg_match('/\{users\s*[-\x{2013}\?]\s*(name|nip|rank|job_title|assignment_regulation_level)\}/u', (string) $textNode->textContent) === 1) {
                return true;
            }
        }

        return false;
    }

    private function setNotaDinasUserRowNumber(DOMXPath $xpath, mixed $rowNode, int $number): void
    {
        $firstCellNodes = $xpath->query('./w:tc[1]', $rowNode);
        if (! $firstCellNodes || $firstCellNodes->length === 0) {
            return;
        }

        $firstCellNode = $firstCellNodes->item(0);
        if (! $firstCellNode) {
            return;
        }

        if (trim($this->getParagraphText($xpath, $firstCellNode)) !== '') {
            return;
        }

        $textNodes = $xpath->query('.//w:t', $firstCellNode);
        if ($textNodes && $textNodes->length > 0) {
            $textNodes->item(0)->textContent = (string) $number;
            for ($i = 1; $i < $textNodes->length; $i++) {
                $textNodes->item($i)->nodeValue = '';
            }

            return;
        }

        $paragraphNodes = $xpath->query('./w:p', $firstCellNode);
        $paragraphNode = ($paragraphNodes && $paragraphNodes->length > 0)
            ? $paragraphNodes->item(0)
            : null;

        $document = $rowNode->ownerDocument;
        if (! $document instanceof DOMDocument) {
            return;
        }

        if (! $paragraphNode) {
            $paragraphNode = $document->createElementNS(self::WORD_NS, 'w:p');
            $firstCellNode->appendChild($paragraphNode);
        }

        $runNode = $document->createElementNS(self::WORD_NS, 'w:r');
        $textNode = $document->createElementNS(self::WORD_NS, 'w:t');
        $textNode->textContent = (string) $number;
        $runNode->appendChild($textNode);
        $paragraphNode->appendChild($runNode);
    }

    private function buildAttendedsRankKalselText(Assignment $assignment): string
    {
        $attendeds = $assignment->relationLoaded('attendeds')
            ? $assignment->attendeds
            : $assignment->attendeds()->get();

        $ranks = $attendeds
            ->pluck('rank')
            ->map(fn ($rank) => trim((string) $rank))
            ->filter()
            ->values();

        if ($ranks->isEmpty()) {
            return '-';
        }

        return $ranks->implode(', ').' Kalsel,';
    }

    private function pageBreakXml(): string
    {
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    private function createNumberingContext(?string $numberingXml): ?array
    {
        if (! is_string($numberingXml) || trim($numberingXml) === '') {
            return null;
        }

        $dom = new DOMDocument;
        if (! $dom->loadXML($numberingXml)) {
            return null;
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $root = $xpath->query('/w:numbering')->item(0);
        if (! $root) {
            return null;
        }

        $numToAbstractMap = [];
        $maxNumId = 0;

        foreach ($xpath->query('//w:num') as $numNode) {
            $numId = (int) $this->getWordAttributeValue($numNode, 'numId');
            if ($numId <= 0) {
                continue;
            }

            $abstractNumNode = $xpath->query('./w:abstractNumId', $numNode)->item(0);
            $abstractNumId = (int) $this->getWordAttributeValue($abstractNumNode, 'val');

            $numToAbstractMap[$numId] = $abstractNumId;
            $maxNumId = max($maxNumId, $numId);
        }

        return [
            'dom' => $dom,
            'root' => $root,
            'num_to_abstract_map' => $numToAbstractMap,
            'max_num_id' => $maxNumId,
        ];
    }

    private function remapPageNumbering(string $pageXml, array &$numberingContext): string
    {
        preg_match_all('/w:numId\s+w:val="(\d+)"/', $pageXml, $matches);
        $oldNumIds = array_values(array_unique(array_map('intval', $matches[1] ?? [])));

        if ($oldNumIds === []) {
            return $pageXml;
        }

        $pageNumMap = [];
        foreach ($oldNumIds as $oldNumId) {
            $abstractNumId = $numberingContext['num_to_abstract_map'][$oldNumId] ?? null;
            if ($abstractNumId === null) {
                continue;
            }

            $numberingContext['max_num_id']++;
            $newNumId = (int) $numberingContext['max_num_id'];
            $pageNumMap[$oldNumId] = $newNumId;

            $this->appendNumberingDefinition($numberingContext, $newNumId, (int) $abstractNumId);
        }

        foreach ($pageNumMap as $oldNumId => $newNumId) {
            $pageXml = preg_replace(
                '/w:numId\s+w:val="'.$oldNumId.'"/',
                'w:numId w:val="'.$newNumId.'"',
                $pageXml
            ) ?? $pageXml;
        }

        return $pageXml;
    }

    private function appendNumberingDefinition(array &$numberingContext, int $newNumId, int $abstractNumId): void
    {
        $dom = $numberingContext['dom'];

        $numNode = $dom->createElementNS(self::WORD_NS, 'w:num');
        $numNode->setAttributeNS(self::WORD_NS, 'w:numId', (string) $newNumId);

        $abstractNumNode = $dom->createElementNS(self::WORD_NS, 'w:abstractNumId');
        $abstractNumNode->setAttributeNS(self::WORD_NS, 'w:val', (string) $abstractNumId);
        $numNode->appendChild($abstractNumNode);

        $levelOverrideNode = $dom->createElementNS(self::WORD_NS, 'w:lvlOverride');
        $levelOverrideNode->setAttributeNS(self::WORD_NS, 'w:ilvl', '0');

        $startOverrideNode = $dom->createElementNS(self::WORD_NS, 'w:startOverride');
        $startOverrideNode->setAttributeNS(self::WORD_NS, 'w:val', '1');
        $levelOverrideNode->appendChild($startOverrideNode);

        $numNode->appendChild($levelOverrideNode);
        $numberingContext['root']->appendChild($numNode);
    }

    private function getWordAttributeValue(mixed $node, string $attribute): string
    {
        if (! $node) {
            return '';
        }

        $value = $node->getAttributeNS(self::WORD_NS, $attribute);
        if ($value !== '') {
            return $value;
        }

        $value = $node->getAttribute('w:'.$attribute);
        if ($value !== '') {
            return $value;
        }

        return $node->getAttribute($attribute);
    }

    private function valueOrDash(mixed $value, mixed $fallback = null): string
    {
        $stringValue = trim((string) ($value ?? ''));
        if ($stringValue !== '') {
            return $stringValue;
        }

        $fallbackValue = trim((string) ($fallback ?? ''));

        return $fallbackValue !== '' ? $fallbackValue : '-';
    }

    private function formatIndonesianDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $month = $months[(int) $date->format('n')] ?? $date->format('m');

        return $date->format('d').' '.$month.' '.$date->format('Y');
    }

    private function formatDayCount(int $dayCount): string
    {
        if ($dayCount < 1) {
            $dayCount = 1;
        }

        $spelled = $this->spellNumber($dayCount);

        return $dayCount.' ('.$spelled.')';
    }

    private function spellNumber(int $number): string
    {
        $words = [
            0 => 'nol',
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
            5 => 'lima',
            6 => 'enam',
            7 => 'tujuh',
            8 => 'delapan',
            9 => 'sembilan',
            10 => 'sepuluh',
            11 => 'sebelas',
        ];

        if ($number < 12) {
            return $words[$number];
        }

        if ($number < 20) {
            return $this->spellNumber($number - 10).' belas';
        }

        if ($number < 100) {
            $tens = intdiv($number, 10);
            $rest = $number % 10;
            $result = $this->spellNumber($tens).' puluh';
            if ($rest > 0) {
                $result .= ' '.$this->spellNumber($rest);
            }

            return $result;
        }

        return (string) $number;
    }
}
