<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Dashboard Ringkasan</h2>
            <span class="text-sm text-slate-500">Periode: {{ $periodLabel }}</span>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell">
            <x-flash-alerts />

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="panel p-5">
                    <p class="mb-2 text-xs uppercase tracking-wide text-slate-500">Jumlah Staff</p>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($totalStaff, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-slate-500">Aktif: {{ number_format($activeStaff, 0, ',', '.') }}</p>
                </div>
                <div class="panel p-5">
                    <p class="mb-2 text-xs uppercase tracking-wide text-slate-500">Penugasan Bulan Ini</p>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($monthlyAssignmentCount, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Total personel ditugaskan: {{ number_format($monthlyAssignmentUserCount, 0, ',', '.') }}
                    </p>
                </div>
                <div class="panel p-5">
                    <p class="mb-2 text-xs uppercase tracking-wide text-slate-500">Anggaran Bulan Ini</p>
                    <p class="text-2xl font-bold text-emerald-700">Rp {{ number_format($monthlyBudgetTotal, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Dalam daerah: Rp {{ number_format($budgetByRegion['dalam_daerah'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="panel p-5">
                    <p class="mb-2 text-xs uppercase tracking-wide text-slate-500">Anggaran Luar Daerah Kabupaten</p>
                    <p class="text-2xl font-bold text-slate-800">
                        Rp {{ number_format($budgetByRegion['luar_daerah_kabupaten'] ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        Luar daerah: Rp {{ number_format($budgetByRegion['luar_daerah'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="panel p-6">
                    <h3 class="mb-4 text-lg font-semibold text-slate-900">Staff Paling Sering Ditugaskan (Per Wilayah)</h3>
                    <div class="space-y-4">
                        @foreach ($regionLabels as $regionKey => $regionLabel)
                            @php $rows = $topStaffByRegion[$regionKey] ?? collect(); @endphp
                            <div class="panel-soft p-4">
                                <p class="mb-3 text-sm font-semibold text-slate-700">{{ $regionLabel }}</p>
                                <div class="overflow-x-auto">
                                    <table class="table-ui">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Staff</th>
                                                <th class="text-right">Jumlah Tugas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rows as $index => $row)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="font-semibold text-slate-900">{{ $row->name }}</td>
                                                    <td class="text-right">{{ number_format($row->total_assignments, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-4 text-center text-slate-500">
                                                        Belum ada penugasan pada periode ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel p-6">
                    <h3 class="mb-4 text-lg font-semibold text-slate-900">
                        Anggaran yang Perlu Disiapkan per Bulan ({{ $yearNow }})
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="table-ui">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-right">Anggaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyBudgetTable as $row)
                                    <tr>
                                        <td>{{ $row->month_label }}</td>
                                        <td class="text-right font-semibold text-slate-900">
                                            Rp {{ number_format($row->total_budget, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">
                    Pendapatan Staff Bulan Ini (Dalam Daerah dan Luar Daerah Kabupaten)
                </h3>
                <div class="overflow-x-auto">
                    <table class="table-ui">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Staff</th>
                                <th class="text-right">Dalam Daerah</th>
                                <th class="text-right">Luar Daerah Kabupaten</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staffIncomeRows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-semibold text-slate-900">{{ $row->name }}</td>
                                    <td class="text-right">Rp {{ number_format($row->income_dalam_daerah, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($row->income_luar_daerah_kabupaten, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-slate-900">Rp {{ number_format($row->total_income, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-500">
                                        Belum ada data pendapatan staff pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
