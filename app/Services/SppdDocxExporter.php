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

    public function export(Assignment $assignment): string
    {
        $templatePath = base_path('LEMBAR_SPPD.docx');
        if (! File::exists($templatePath)) {
            throw new RuntimeException('Template LEMBAR_SPPD.docx tidak ditemukan di root project.');
        }

        $users = $assignment->assignmentUsers
            ->pluck('user')
            ->filter(fn($user) => $user instanceof User)
            ->values();

        if ($users->isEmpty()) {
            throw new RuntimeException('Data petugas belum tersedia untuk assignment ini.');
        }

        $templateZip = new ZipArchive();
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
            $users
        );

        $tempDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tempDir);

        $safeCode = Str::slug((string) $assignment->code ?: 'assignment');
        $fileName = "sppd-{$safeCode}-" . now()->format('YmdHis') . '.docx';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

        if (! copy($templatePath, $outputPath)) {
            throw new RuntimeException('Gagal menyalin template SPPD.');
        }

        $outputZip = new ZipArchive();
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

    private function buildMergedDocumentXml(
        string $documentXml,
        ?string $numberingXml,
        Assignment $assignment,
        Collection $users
    ): array {
        [$documentOpenTag, $templateBodyXml, $sectPrXml] = $this->extractTemplateParts($documentXml);
        $numberingContext = $this->createNumberingContext($numberingXml);

        $pages = [];
        $sheetNumber = 1;

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

        $bodyInnerXml = implode($this->pageBreakXml(), $pages) . $sectPrXml;
        $updatedXml = preg_replace(
            '/<w:body>.*<\/w:body>/su',
            '<w:body>' . $bodyInnerXml . '</w:body>',
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

        $dom = new DOMDocument();
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
            . $documentOpenTag
            . '<w:body>'
            . $templateBodyXml
            . '</w:body></w:document>';

        $dom = new DOMDocument();
        if (! $dom->loadXML($pageXml)) {
            throw new RuntimeException('Gagal memuat halaman template SPPD.');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $values = $this->buildReplacementValues($assignment, $user, $sheetNumber);
        $legacyQueue = [
            $values['user_assignment_regulation_level'],
            $values['assignment_transportation'],
            $values['assignment_boarding_date'],
            $values['assignment_return_date'],
        ];

        foreach ($xpath->query('//w:body//w:p') as $paragraph) {
            $textNodes = $xpath->query('.//w:t', $paragraph);
            if (! $textNodes || $textNodes->length === 0) {
                continue;
            }

            $originalText = '';
            foreach ($textNodes as $textNode) {
                $originalText .= $textNode->textContent;
            }

            $updatedText = $this->replaceKnownPlaceholders($originalText, $values);
            $updatedText = $this->replaceLegacyPlaceholders($updatedText, $legacyQueue);

            if ($updatedText === $originalText) {
                continue;
            }

            $textNodes->item(0)->nodeValue = $updatedText;
            for ($i = 1; $i < $textNodes->length; $i++) {
                $textNodes->item($i)->nodeValue = '';
            }
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

    private function replaceKnownPlaceholders(string $text, array $values): string
    {
        $patterns = [
            '/\{users\s*[-\x{2013}]\s*name\}/u' => $values['user_name'],
            '/\{users\s*[-\x{2013}]\s*nip\}/u' => $values['user_nip'],
            '/\{users\s*[-\x{2013}]\s*rank\}/u' => $values['user_rank'],
            '/\{users\s*[-\x{2013}]\s*job_title\}/u' => $values['user_job_title'],
            '/\{users\s*[-\x{2013}]\s*assignment_regulation_level\}/u' => $values['user_assignment_regulation_level'],
            '/\{assignments\s*[-\x{2013}]\s*title\}/u' => $values['assignment_title'],
            '/\{assignments\s*[-\x{2013}]\s*date\}/u' => $values['assignment_date'],
            '/\{assignments\s*[-\x{2013}]\s*location_detail\}/u' => $values['assignment_location_detail'],
            '/\{assignments\s*[-\x{2013}]\s*location\}/u' => $values['assignment_location'],
            '/\{assignments\s*[-\x{2013}]\s*day_count\}/u' => $values['assignment_day_count'],
            '/\{assignments\s*[-\x{2013}]\s*transportation\}/u' => $values['assignment_transportation'],
            '/\{assignments\s*[-\x{2013}]\s*boarding_date\}/u' => $values['assignment_boarding_date'],
            '/\{assignments\s*[-\x{2013}]\s*return_date\}/u' => $values['assignment_return_date'],
            '/\{assignments\s*[-\x{2013}]\s*description\}/u' => $values['assignment_description'],
            '/\{assignments\s*[-\x{2013}]\s*date\s*\(\s*dikurangi\s+satu\s+hari\s*\)\}/u' => $values['assignment_issue_date'],
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

    private function buildReplacementValues(Assignment $assignment, User $user, int $sheetNumber): array
    {
        $assignmentDate = $assignment->date instanceof Carbon
            ? $assignment->date->copy()
            : Carbon::parse($assignment->date);

        $issueDate = $assignmentDate->copy()->subDay();

        return [
            'user_name' => $this->valueOrDash($user->name),
            'user_nip' => $this->valueOrDash($user->nip),
            'user_rank' => $this->valueOrDash($user->rank),
            'user_job_title' => $this->valueOrDash($user->job_title),
            'user_assignment_regulation_level' => $this->valueOrDash($user->assignment_regulation_level),
            'assignment_code' => $this->valueOrDash($assignment->code),
            'assignment_number' => $this->valueOrDash($assignment->code),
            'assignment_title' => $this->valueOrDash($assignment->title),
            'assignment_date' => $this->formatIndonesianDate($assignmentDate),
            'assignment_issue_date' => $this->formatIndonesianDate($issueDate),
            'assignment_location_detail' => $this->valueOrDash($assignment->location_detail, $assignment->location),
            'assignment_location' => $this->valueOrDash($assignment->location),
            'assignment_day_count' => $this->formatDayCount((int) $assignment->day_count),
            'assignment_boarding_date' => $this->formatIndonesianDate($assignment->boarding_date),
            'assignment_return_date' => $this->formatIndonesianDate($assignment->return_date),
            'assignment_transportation' => $this->valueOrDash($assignment->transportation),
            'assignment_description' => $this->valueOrDash($assignment->description),
            'sheet_number' => (string) $sheetNumber,
        ];
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

        $dom = new DOMDocument();
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
                '/w:numId\s+w:val="' . $oldNumId . '"/',
                'w:numId w:val="' . $newNumId . '"',
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

        $value = $node->getAttribute('w:' . $attribute);
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

        return $date->format('j') . ' ' . $month . ' ' . $date->format('Y');
    }

    private function formatDayCount(int $dayCount): string
    {
        if ($dayCount < 1) {
            $dayCount = 1;
        }

        $spelled = $this->spellNumber($dayCount);

        return $dayCount . ' (' . $spelled . ')';
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
            return $this->spellNumber($number - 10) . ' belas';
        }

        if ($number < 100) {
            $tens = intdiv($number, 10);
            $rest = $number % 10;
            $result = $this->spellNumber($tens) . ' puluh';
            if ($rest > 0) {
                $result .= ' ' . $this->spellNumber($rest);
            }

            return $result;
        }

        return (string) $number;
    }
}
