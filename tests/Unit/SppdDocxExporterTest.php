<?php

use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\User;
use App\Services\SppdDocxExporter;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

uses(Tests\TestCase::class);

function invokeExporterMethod(string $method, array $arguments = []): mixed
{
    $reflection = new ReflectionClass(SppdDocxExporter::class);
    $instance = $reflection->newInstance();
    $methodReflection = $reflection->getMethod($method);
    $methodReflection->setAccessible(true);

    return $methodReflection->invokeArgs($instance, $arguments);
}

function replacementValues(string $assignmentDatePeriod): array
{
    return [
        'assignment_date_period' => $assignmentDatePeriod,
        'assignment_end_date' => '15 Maret 2026',
        'assignment_period_year' => '2026',
        'assignment_location_detail' => 'Kantor Gubernur',
        'assignment_location' => 'Banjarbaru',
        'assignment_date' => '13 Maret 2026',
        'assignment_title' => 'Rapat Koordinasi',
        'assignment_issue_date' => '12 Maret 2026',
        'assignment_region_classification' => 'Dalam Daerah',
        'assignment_day_count' => '3 (tiga)',
        'assignment_boarding_date' => '13 Maret 2026',
        'assignment_return_date' => '15 Maret 2026',
        'assignment_transportation' => '-',
        'assignment_description' => '-',
        'assignment_code' => '001/ADPIM/2026',
        'assignment_number' => '001/ADPIM/2026',
        'sheet_number' => '1',
        'user_name' => '-',
        'user_nip' => '-',
        'user_rank' => '-',
        'user_job_title' => '-',
        'user_assignment_regulation_level' => '-',
        'attendeds_rank_kalsel' => '-',
    ];
}

it('formats spt date as a single day when day_count is one', function () {
    $formattedPeriod = invokeExporterMethod('formatAssignmentDatePeriod', [
        Carbon::parse('2026-03-13'),
        Carbon::parse('2026-03-13'),
        1,
    ]);
    $values = replacementValues($formattedPeriod);
    $dash = "\u{2013}";

    $text = "Tanggal {assignments - date} s/d {assignments {$dash} date} + {assignments {$dash} day_count - 1} {years {$dash} now()} di {assignments - location_detail}, {assignments - location}.";
    $result = invokeExporterMethod('replaceKnownPlaceholders', [$text, $values]);

    expect($result)->toBe('Tanggal 13 Maret 2026 di Kantor Gubernur, Banjarbaru.');
});

it('formats spt date as a period with the year shown once when day_count is multiple days', function () {
    $formattedPeriod = invokeExporterMethod('formatAssignmentDatePeriod', [
        Carbon::parse('2026-03-13'),
        Carbon::parse('2026-03-15'),
        3,
    ]);
    $values = replacementValues($formattedPeriod);
    $dash = "\u{2013}";

    $text = "Tanggal {assignments - date} s/d {assignments {$dash} date} + {assignments {$dash} day_count - 1} {years {$dash} now()} di {assignments - location_detail}, {assignments - location}.";
    $result = invokeExporterMethod('replaceKnownPlaceholders', [$text, $values]);

    expect($result)->toBe('Tanggal 13 Maret s.d 15 Maret 2026 di Kantor Gubernur, Banjarbaru.');
});

it('includes npd, lembar spt hierarki, and lembar spt in dalam daerah exports', function () {
    $assignment = new Assignment([
        'code' => '001/ADPIM/2026',
        'title' => 'Rapat Koordinasi',
        'agency' => 'Biro Administrasi Pimpinan',
        'date' => '2026-03-13',
        'boarding_date' => '2026-03-13',
        'transportation' => 'Mobil Dinas',
        'time' => '08.00 WITA',
        'day_count' => 1,
        'location' => 'Banjarbaru',
        'location_detail' => 'Kantor Gubernur',
        'fee_per_day' => 0,
        'description' => 'Koordinasi internal',
        'region_classification' => 'dalam_daerah',
    ]);
    $assignment->id = 1;
    $assignment->exists = true;
    $assignment->setRelation('attendeds', collect());

    $user = new User([
        'name' => 'Pengguna Uji',
        'nip' => '198001012006041001',
        'rank' => 'Penata',
        'job_title' => 'Analis',
        'assignment_regulation_level' => 'C',
        'email' => 'uji@example.test',
    ]);

    $assignmentUser = new AssignmentUser([
        'assignment_id' => 1,
    ]);
    $assignmentUser->setRelation('user', $user);

    $assignment->setRelation('assignmentUsers', collect([$assignmentUser]));

    $outputPath = (new SppdDocxExporter())->export($assignment);

    expect(File::exists($outputPath))->toBeTrue();

    $zip = new ZipArchive;
    $openResult = $zip->open($outputPath);
    expect($openResult)->toBeTrue();

    $documentXml = $zip->getFromName('word/document.xml');
    $zip->close();
    File::delete($outputPath);

    expect($documentXml)->toBeString()
        ->and($documentXml)->toContain('NOTA DINAS')
        ->and($documentXml)->toContain('PARAF HIERARKI')
        ->and($documentXml)->toContain('Rapat Koordinasi')
        ->and(substr_count($documentXml, 'SURAT PERINTAH TUGAS'))->toBe(2);
});
