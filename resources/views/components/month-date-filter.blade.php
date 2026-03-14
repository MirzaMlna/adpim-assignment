@props([
    'month' => null,
    'date' => null,
    'title' => 'Filter Periode',
    'description' => 'Pilih bulan terlebih dahulu, lalu sempitkan lagi ke tanggal tertentu bila diperlukan.',
])

@php
    $summaryLabel = 'Semua periode';

    if ($date) {
        $summaryLabel = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->translatedFormat('d M Y');
    } elseif ($month) {
        $summaryLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
    }
@endphp

<div x-data="{
    selectedMonth: @js($month),
    selectedDate: @js($date),
    syncDateWithinMonth() {
        if (!this.selectedMonth) {
            this.selectedDate = '';
            return;
        }

        if (this.selectedDate && !this.selectedDate.startsWith(this.selectedMonth)) {
            this.selectedDate = '';
        }
    },
    minDate() {
        return this.selectedMonth ? `${this.selectedMonth}-01` : null;
    },
    maxDate() {
        if (!this.selectedMonth) {
            return null;
        }

        const [year, month] = this.selectedMonth.split('-').map(Number);
        const lastDay = new Date(year, month, 0).getDate();

        return `${this.selectedMonth}-${String(lastDay).padStart(2, '0')}`;
    }
}" class="filter-shell mb-4">
    <form method="GET" action="{{ url()->current() }}"
        class="flex flex-col gap-4 p-4 sm:p-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex items-start gap-3">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-base text-white shadow-sm">
                <i class="bi bi-funnel"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
                    <span class="filter-chip">{{ $summaryLabel }}</span>
                </div>
                <p class="mt-1 hidden text-xs text-slate-500 sm:block">{{ $description }}</p>
            </div>
        </div>

        @foreach (request()->except(['month', 'date', 'page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $nestedValue)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[32rem]">
            <div class="filter-group">
                <label for="filter-month" class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <i class="bi bi-calendar3 text-sm"></i>
                    Bulan
                </label>
                <input id="filter-month" type="month" name="month" x-model="selectedMonth"
                    @change="syncDateWithinMonth()" class="filter-input">
            </div>

            <div class="filter-group">
                <label for="filter-date" class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <i class="bi bi-calendar-date text-sm"></i>
                    Tanggal
                </label>
                <input id="filter-date" type="date" name="date" x-model="selectedDate" :min="minDate()"
                    :max="maxDate()" :disabled="!selectedMonth"
                    class="filter-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
            </div>
        </div>

        <div class="flex flex-wrap gap-2 xl:justify-end">
            <button type="submit" class="btn btn-primary min-w-28">
                <i class="bi bi-check2-circle"></i>
                Terapkan
            </button>
            <a href="{{ url()->current() }}" class="btn btn-secondary min-w-28">
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset
            </a>
        </div>
    </form>
</div>
