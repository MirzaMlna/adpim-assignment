<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-800">
                Dashboard Ringkasan
            </h2>
            <span class="text-sm text-slate-500">
                Periode: {{ $periodLabel }}
            </span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl border shadow-sm p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                        Jumlah Staff
                    </p>
                    <p class="text-3xl font-bold text-slate-800">
                        {{ number_format($totalStaff, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Aktif: {{ number_format($activeStaff, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl border shadow-sm p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                        Penugasan Bulan Ini
                    </p>
                    <p class="text-3xl font-bold text-slate-800">
                        {{ number_format($monthlyAssignmentCount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Total personel ditugaskan: {{ number_format($monthlyAssignmentUserCount, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl border shadow-sm p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                        Anggaran Bulan Ini
                    </p>
                    <p class="text-2xl font-bold text-emerald-700">
                        Rp {{ number_format($monthlyBudgetTotal, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Dalam daerah: Rp {{ number_format($budgetByRegion['dalam_daerah'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl border shadow-sm p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                        Anggaran Dalam Daerah Kabupaten
                    </p>
                    <p class="text-2xl font-bold text-sky-700">
                        Rp {{ number_format($budgetByRegion['dalam_daerah_kabupaten'] ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Luar daerah: Rp {{ number_format($budgetByRegion['luar_daerah'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-2xl border shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        Staff Paling Sering Ditugaskan (Per Wilayah)
                    </h3>

                    <div class="space-y-4">
                        @foreach ($regionLabels as $regionKey => $regionLabel)
                            @php $rows = $topStaffByRegion[$regionKey] ?? collect(); @endphp

                            <div class="border rounded-xl p-4 bg-slate-50">
                                <p class="text-sm font-semibold text-slate-700 mb-3">
                                    {{ $regionLabel }}
                                </p>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm bg-white rounded-lg overflow-hidden">
                                        <thead class="bg-slate-800 text-white">
                                            <tr>
                                                <th class="px-3 py-2 text-left">No</th>
                                                <th class="px-3 py-2 text-left">Nama Staff</th>
                                                <th class="px-3 py-2 text-right">Jumlah Tugas</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            @forelse ($rows as $index => $row)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="px-3 py-2">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-2 font-medium text-slate-700">
                                                        {{ $row->name }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        {{ number_format($row->total_assignments, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-3 py-3 text-center text-slate-500">
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

                <div class="bg-white rounded-2xl border shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        Anggaran yang Perlu Disiapkan per Bulan ({{ $yearNow }})
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="px-3 py-2 text-left">Bulan</th>
                                    <th class="px-3 py-2 text-right">Anggaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($monthlyBudgetTable as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-3 py-2">
                                            {{ $row->month_label }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium">
                                            Rp {{ number_format($row->total_budget, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    Pendapatan Staff Bulan Ini (Dalam Daerah & Dalam Daerah Kabupaten)
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-3 py-2 text-left">No</th>
                                <th class="px-3 py-2 text-left">Nama Staff</th>
                                <th class="px-3 py-2 text-right">Dalam Daerah</th>
                                <th class="px-3 py-2 text-right">Dalam Daerah Kabupaten</th>
                                <th class="px-3 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($staffIncomeRows as $index => $row)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 font-medium text-slate-700">{{ $row->name }}</td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($row->income_dalam_daerah, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($row->income_dalam_daerah_kabupaten, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold text-slate-800">
                                        Rp {{ number_format($row->total_income, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-slate-500">
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
