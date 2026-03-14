<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

trait AppliesMonthDateFilters
{
    protected function resolveMonthDateFilters(Request $request, ?string $defaultMonth = null): array
    {
        $selectedMonth = $this->normalizeMonthFilter($request->query('month'));
        $selectedDate = $this->normalizeDateFilter($request->query('date'));

        if (! $selectedMonth && $defaultMonth) {
            $selectedMonth = $this->normalizeMonthFilter($defaultMonth);
        }

        if ($selectedDate) {
            $selectedMonth = Carbon::createFromFormat('Y-m-d', $selectedDate)->format('Y-m');
        }

        $monthStart = null;
        $monthEnd = null;

        if ($selectedMonth) {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
        }

        return [
            'month' => $selectedMonth,
            'date' => $selectedDate,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'has_filter' => filled($selectedMonth) || filled($selectedDate),
        ];
    }

    protected function applyMonthDateFilters(object $query, array $filters, string $column): void
    {
        if ($filters['month_start']) {
            $query
                ->whereYear($column, $filters['month_start']->year)
                ->whereMonth($column, $filters['month_start']->month);
        }

        if ($filters['date']) {
            $query->whereDate($column, $filters['date']);
        }
    }

    private function normalizeMonthFilter(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $month = Carbon::createFromFormat('!Y-m', $value);

            return $month->format('Y-m') === $value ? $value : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeDateFilter(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);

            return $date->format('Y-m-d') === $value ? $value : null;
        } catch (Throwable) {
            return null;
        }
    }
}
