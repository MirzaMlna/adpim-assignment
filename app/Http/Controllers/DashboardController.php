<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesMonthDateFilters;
use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AppliesMonthDateFilters;

    public function index(Request $request): View
    {
        $filters = $this->resolveMonthDateFilters($request, now()->format('Y-m'));
        $monthStart = $filters['month_start'] ?? now()->startOfMonth();
        $monthEnd = $filters['month_end'] ?? $monthStart->copy()->endOfMonth();
        $regionLabels = $this->regionLabels();
        $cacheKey = 'dashboard:summary:'.$monthStart->format('Y-m').':'.($filters['date'] ?? 'all');

        $cachedData = Cache::get($cacheKey);
        if (is_array($cachedData)) {
            return view('dashboard', $cachedData);
        }

        $staffBaseQuery = User::query()->where('role', 'STAFF');
        $totalStaff = (clone $staffBaseQuery)->count();
        $activeStaff = (clone $staffBaseQuery)->where('is_active', true)->count();

        $monthlyTopRowsQuery = AssignmentUser::query()
            ->join('assignments', 'assignment_users.assignment_id', '=', 'assignments.id')
            ->join('users', 'assignment_users.user_id', '=', 'users.id')
            ->where('users.role', 'STAFF')
            ->select([
                'assignments.region_classification',
                'assignment_users.user_id',
                'users.name',
            ])
            ->selectRaw('COUNT(*) as total_assignments')
            ->groupBy('assignments.region_classification', 'assignment_users.user_id', 'users.name')
            ->orderBy('assignments.region_classification')
            ->orderByDesc('total_assignments')
            ->orderBy('users.name');

        $this->applyMonthDateFilters($monthlyTopRowsQuery, $filters, 'assignments.date');
        $monthlyTopRows = $monthlyTopRowsQuery->get();

        $topStaffByRegion = [];
        foreach ($regionLabels as $regionKey => $regionLabel) {
            $rows = $monthlyTopRows
                ->where('region_classification', $regionKey)
                ->values();

            if ($rows->isEmpty()) {
                $topStaffByRegion[$regionKey] = collect();

                continue;
            }

            $maxAssignments = (int) $rows->max('total_assignments');
            $topStaffByRegion[$regionKey] = $rows
                ->where('total_assignments', $maxAssignments)
                ->values();
        }

        $incomeRowsQuery = AssignmentUser::query()
            ->join('assignments', 'assignment_users.assignment_id', '=', 'assignments.id')
            ->join('users', 'assignment_users.user_id', '=', 'users.id')
            ->where('users.role', 'STAFF')
            ->whereIn('assignments.region_classification', ['dalam_daerah', 'luar_daerah_kabupaten'])
            ->select(['assignment_users.user_id', 'users.name'])
            ->selectRaw("SUM(CASE WHEN assignments.region_classification = 'dalam_daerah' THEN assignments.fee_per_day * assignments.day_count ELSE 0 END) as income_dalam_daerah")
            ->selectRaw("SUM(CASE WHEN assignments.region_classification = 'luar_daerah_kabupaten' THEN assignments.fee_per_day * assignments.day_count ELSE 0 END) as income_luar_daerah_kabupaten")
            ->groupBy('assignment_users.user_id', 'users.name');

        $this->applyMonthDateFilters($incomeRowsQuery, $filters, 'assignments.date');
        $incomeRows = $incomeRowsQuery
            ->get()
            ->keyBy('user_id');

        $staffIncomeRows = User::query()
            ->where('role', 'STAFF')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (User $staff) use ($incomeRows) {
                $income = $incomeRows->get($staff->id);
                $incomeDalamDaerah = (float) ($income->income_dalam_daerah ?? 0);
                $incomeLuarDaerahKabupaten = (float) ($income->income_luar_daerah_kabupaten ?? 0);

                return (object) [
                    'user_id' => $staff->id,
                    'name' => $staff->name,
                    'income_dalam_daerah' => $incomeDalamDaerah,
                    'income_luar_daerah_kabupaten' => $incomeLuarDaerahKabupaten,
                    'total_income' => $incomeDalamDaerah + $incomeLuarDaerahKabupaten,
                ];
            })
            ->sortByDesc('total_income')
            ->values();

        $monthlyAssignmentsQuery = Assignment::query()->withCount('assignmentUsers');
        $this->applyMonthDateFilters($monthlyAssignmentsQuery, $filters, 'date');
        $monthlyAssignments = $monthlyAssignmentsQuery->get(['id', 'date', 'fee_per_day', 'day_count', 'region_classification']);

        $budgetByRegion = collect(array_fill_keys(array_keys($regionLabels), 0.0));
        $monthlyBudgetTotal = 0.0;
        $assignmentUserCount = 0;

        foreach ($monthlyAssignments as $assignment) {
            $personCount = (int) $assignment->assignment_users_count;
            $assignmentUserCount += $personCount;

            $assignmentBudget = (float) $assignment->fee_per_day
                * max(1, (int) $assignment->day_count)
                * $personCount;

            $monthlyBudgetTotal += $assignmentBudget;
            $regionKey = (string) $assignment->region_classification;

            if ($budgetByRegion->has($regionKey)) {
                $budgetByRegion[$regionKey] += $assignmentBudget;
            }
        }

        $yearNow = (int) $monthStart->year;
        $yearAssignments = Assignment::query()
            ->withCount('assignmentUsers')
            ->whereYear('date', $yearNow)
            ->get(['id', 'date', 'fee_per_day', 'day_count']);

        $monthlyBudgetSeries = collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => 0.0]);

        foreach ($yearAssignments as $assignment) {
            $monthNum = (int) Carbon::parse($assignment->date)->format('n');
            $personCount = (int) $assignment->assignment_users_count;
            $assignmentBudget = (float) $assignment->fee_per_day
                * max(1, (int) $assignment->day_count)
                * $personCount;

            $monthlyBudgetSeries[$monthNum] += $assignmentBudget;
        }

        $monthNames = [
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

        $monthlyBudgetTable = $monthlyBudgetSeries
            ->map(fn (float $total, int $monthNum) => (object) [
                'month_num' => $monthNum,
                'month_label' => $monthNames[$monthNum] ?? (string) $monthNum,
                'total_budget' => $total,
            ])
            ->values();

        $viewData = [
            'periodLabel' => $filters['date']
                ? Carbon::createFromFormat('Y-m-d', $filters['date'])->translatedFormat('d F Y')
                : $monthStart->translatedFormat('F Y'),
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'topStaffByRegion' => $topStaffByRegion,
            'regionLabels' => $regionLabels,
            'staffIncomeRows' => $staffIncomeRows,
            'monthlyBudgetTotal' => $monthlyBudgetTotal,
            'budgetByRegion' => $budgetByRegion,
            'monthlyAssignmentCount' => $monthlyAssignments->count(),
            'monthlyAssignmentUserCount' => $assignmentUserCount,
            'yearNow' => $yearNow,
            'monthlyBudgetTable' => $monthlyBudgetTable,
            'filters' => $filters,
        ];

        Cache::put($cacheKey, $viewData, now()->addMinutes(5));

        return view('dashboard', $viewData);
    }

    private function regionLabels(): array
    {
        return [
            'dalam_daerah' => 'Dalam Daerah',
            'luar_daerah_kabupaten' => 'Luar Daerah Kabupaten',
            'luar_daerah' => 'Luar Daerah',
        ];
    }
}
