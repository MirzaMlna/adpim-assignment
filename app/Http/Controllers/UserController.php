<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('subDivision')->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $subDivisions = SubDivision::all();
        return view('users.create', compact('subDivisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_division_id' => 'required|exists:sub_divisions,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nip' => 'required|string|max:255',
            'name' => 'required',
            'rank' => 'required',
            'job_title' => 'required',
            'assignment_regulation_level' => 'nullable|string|max:255',
            'role' => 'required',
        ]);

        User::create([
            'sub_division_id' => $request->sub_division_id,
            'email' => $request->email,
            'password' => $request->password, // otomatis hash
            'nip' => $request->nip,
            'name' => $request->name,
            'rank' => $request->rank,
            'job_title' => $request->job_title,
            'assignment_regulation_level' => $request->assignment_regulation_level,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'note' => $request->note,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:4096',
        ], [
            'file.required' => 'File import wajib dipilih.',
            'file.mimes' => 'File import harus berformat XLSX.',
            'file.max' => 'Ukuran file maksimal 4MB.',
        ]);

        $file = $request->file('file');
        $path = $file?->getRealPath();

        if (! $path || ! is_readable($path)) {
            return back()->with('error', 'File import tidak dapat dibaca.');
        }

        try {
            $rows = $this->readXlsxRows($path);
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'File XLSX tidak valid atau tidak bisa diproses.');
        }

        if ($rows === []) {
            return back()->with('error', 'Sheet XLSX kosong.');
        }

        $firstRow = $rows[0] ?? [];
        if (! is_array($firstRow) || $firstRow === []) {
            return back()->with('error', 'Header XLSX tidak ditemukan.');
        }

        $headers = $this->normalizeImportHeaders($firstRow);
        $requiredHeaders = ['sub_division', 'email', 'password', 'nip', 'name', 'rank', 'job_title', 'role'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if ($missingHeaders !== []) {
            return back()->with(
                'error',
                'Header XLSX belum lengkap. Wajib ada: ' . implode(', ', $requiredHeaders) .
                '. Kurang: ' . implode(', ', $missingHeaders) . '.'
            );
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $seenEmails = [];

        try {
            foreach (array_slice($rows, 1) as $index => $row) {
                $rowNumber = $index + 2;
                if (! is_array($row)) {
                    continue;
                }

                $rowData = [];
                foreach ($headers as $index => $header) {
                    $rowData[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
                }

                // Optional columns may be absent from header; normalize them to null.
                $rowData['assignment_regulation_level'] = $rowData['assignment_regulation_level'] ?? null;
                $rowData['is_active'] = $rowData['is_active'] ?? null;
                $rowData['note'] = $rowData['note'] ?? null;
                $rowData['role'] = $this->normalizeRoleForImport((string) ($rowData['role'] ?? ''));

                $isEmptyRow = collect($rowData)->every(fn($value) => $value === null || $value === '');
                if ($isEmptyRow) {
                    continue;
                }

                $validator = Validator::make($rowData, [
                    'sub_division' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'password' => ['required', 'string', 'min:6'],
                    'nip' => ['required', 'string', 'max:255'],
                    'name' => ['required', 'string', 'max:255'],
                    'rank' => ['required', 'string', 'max:255'],
                    'job_title' => ['required', 'string', 'max:255'],
                    'assignment_regulation_level' => ['nullable', 'string', 'max:255'],
                    'role' => ['required', Rule::in(['ADMIN', 'STAFF', 'PIMPINAN ADPIM'])],
                    'is_active' => ['nullable'],
                    'note' => ['nullable', 'string'],
                ], [
                    'role.in' => 'Role wajib salah satu: ADMIN, STAFF, PIMPINAN ADPIM (contoh: petugas = STAFF).',
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    $errors[] = "Baris {$rowNumber}: " . $validator->errors()->first();
                    continue;
                }

                $email = strtolower((string) $rowData['email']);
                if (in_array($email, $seenEmails, true)) {
                    $skipped++;
                    $errors[] = "Baris {$rowNumber}: Email duplikat di file import ({$email}).";
                    continue;
                }
                $seenEmails[] = $email;

                if (User::where('email', $email)->exists()) {
                    $skipped++;
                    $errors[] = "Baris {$rowNumber}: Email {$email} sudah terdaftar.";
                    continue;
                }

                DB::transaction(function () use ($rowData, $email): void {
                    $subDivision = SubDivision::firstOrCreate([
                        'name' => trim((string) $rowData['sub_division']),
                    ]);

                    User::create([
                        'sub_division_id' => $subDivision->id,
                        'email' => $email,
                        'password' => (string) $rowData['password'],
                        'nip' => (string) $rowData['nip'],
                        'name' => (string) $rowData['name'],
                        'rank' => (string) $rowData['rank'],
                        'job_title' => (string) $rowData['job_title'],
                        'assignment_regulation_level' => $rowData['assignment_regulation_level'] ?: null,
                        'role' => (string) $rowData['role'],
                        'is_active' => $this->toBoolean($rowData['is_active'] ?? null, true),
                        'note' => $rowData['note'] ?: null,
                    ]);
                });

                $created++;
            }
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Proses import gagal. Periksa isi XLSX lalu coba lagi.');
        }

        if ($created === 0) {
            $message = 'Import selesai tanpa data tersimpan.';
            if ($errors !== []) {
                $message .= ' ' . implode(' | ', array_slice($errors, 0, 5));
            }

            return back()->with('warning', $message);
        }

        $successMessage = "Import user selesai. Berhasil: {$created}, Dilewati: {$skipped}.";
        $response = redirect()->route('users.index')->with('success', $successMessage);

        if ($errors !== []) {
            $response->with('warning', implode(' | ', array_slice($errors, 0, 5)));
        }

        return $response;
    }


    public function edit(User $user)
    {
        $subDivisions = SubDivision::all();
        return view('users.edit', compact('user', 'subDivisions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'sub_division_id' => 'required|exists:sub_divisions,id',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'nip' => 'required|string|max:255',
            'name' => 'required',
            'rank' => 'required',
            'job_title' => 'required',
            'assignment_regulation_level' => 'nullable|string|max:255',
            'role' => 'required',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $data['is_active'] = $request->has('is_active');

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function normalizeImportHeaders(array $headers): array
    {
        return array_map(function ($header): string {
            $header = strtolower(trim((string) $header));
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            $header = str_replace([' ', '-'], '_', $header);

            return $header;
        }, $headers);
    }

    private function toBoolean(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'aktif'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'n', 'nonaktif'], true)) {
            return false;
        }

        return $default;
    }

    private function normalizeRoleForImport(string $role): string
    {
        $normalized = strtoupper(trim($role));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/u', ' ', $normalized) ?? $normalized);

        $mapping = [
            'ADMIN' => 'ADMIN',
            'SUPER ADMIN' => 'ADMIN',
            'SUPERADMIN' => 'ADMIN',
            'STAFF' => 'STAFF',
            'PETUGAS' => 'STAFF',
            'USER' => 'STAFF',
            'OPERATOR' => 'STAFF',
            '-' => 'STAFF',
            'PIMPINAN ADPIM' => 'PIMPINAN ADPIM',
            'PIMPINAN' => 'PIMPINAN ADPIM',
            'PIMPINAN BIRO ADPIM' => 'PIMPINAN ADPIM',
        ];

        if ($normalized === '') {
            return 'STAFF';
        }

        if (isset($mapping[$normalized])) {
            return $mapping[$normalized];
        }

        // Fallback aman agar enum role tetap valid saat data sumber tidak konsisten.
        return 'STAFF';
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('XLSX tidak dapat dibuka.');
        }

        $worksheetPath = $this->resolveFirstWorksheetPath($zip);
        if (! $worksheetPath) {
            $zip->close();
            throw new \RuntimeException('Worksheet pertama tidak ditemukan.');
        }

        $sheetXml = $zip->getFromName($worksheetPath);
        if (! is_string($sheetXml) || $sheetXml === '') {
            $zip->close();
            throw new \RuntimeException('Isi worksheet kosong.');
        }

        $sharedStrings = $this->loadSharedStrings($zip);
        $zip->close();

        $sheet = simplexml_load_string($sheetXml);
        if (! $sheet) {
            throw new \RuntimeException('Worksheet tidak valid.');
        }

        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $sheet->xpath('//x:sheetData/x:row') ?: [];

        $rows = [];
        foreach ($rowNodes as $rowNode) {
            $rowValues = [];
            foreach ($rowNode->c as $cellNode) {
                $cellRef = (string) $cellNode['r'];
                preg_match('/[A-Z]+/', $cellRef, $columnMatch);
                if (! isset($columnMatch[0])) {
                    continue;
                }

                $columnIndex = $this->columnLettersToIndex($columnMatch[0]);
                $rowValues[$columnIndex] = trim($this->extractCellValue($cellNode, $sharedStrings));
            }

            if ($rowValues === []) {
                $rows[] = [];
                continue;
            }

            ksort($rowValues);
            $maxIndex = max(array_keys($rowValues));
            $normalized = [];
            for ($i = 0; $i <= $maxIndex; $i++) {
                $normalized[$i] = $rowValues[$i] ?? '';
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    private function resolveFirstWorksheetPath(\ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! is_string($workbookXml) || ! is_string($relsXml)) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if (! $workbook || ! $rels) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rels->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $sheetNodes = $workbook->xpath('//x:sheets/x:sheet');
        $firstSheet = $sheetNodes[0] ?? null;
        if (! $firstSheet) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationId = (string) $firstSheet->attributes('r', true)->id;
        if ($relationId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] !== $relationId) {
                continue;
            }

            $target = (string) $relationship['Target'];
            if ($target === '') {
                break;
            }

            return 'xl/' . ltrim(str_replace('\\', '/', $target), '/');
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function loadSharedStrings(\ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if (! is_string($sharedStringsXml) || $sharedStringsXml === '') {
            return [];
        }

        $sharedStrings = simplexml_load_string($sharedStringsXml);
        if (! $sharedStrings) {
            return [];
        }

        $sharedStrings->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $stringItems = $sharedStrings->xpath('//x:si') ?: [];

        $values = [];
        foreach ($stringItems as $item) {
            if (isset($item->t)) {
                $values[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $values[] = $text;
        }

        return $values;
    }

    private function extractCellValue(\SimpleXMLElement $cellNode, array $sharedStrings): string
    {
        $type = (string) $cellNode['t'];

        if ($type === 's') {
            $sharedIndex = (int) ($cellNode->v ?? 0);
            return isset($sharedStrings[$sharedIndex]) ? (string) $sharedStrings[$sharedIndex] : '';
        }

        if ($type === 'inlineStr') {
            return (string) ($cellNode->is->t ?? '');
        }

        if ($type === 'b') {
            return ((string) ($cellNode->v ?? '0')) === '1' ? '1' : '0';
        }

        return (string) ($cellNode->v ?? '');
    }

    private function columnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }
}
