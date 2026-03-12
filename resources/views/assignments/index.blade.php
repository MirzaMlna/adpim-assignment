<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Data Giat</h2>
            <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Tambah Giat
            </a>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell">
            <x-flash-alerts />

            <div class="table-shell">
                <table class="table-ui">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Judul</th>
                            <th>Pimpinan</th>
                            <th>Tanggal</th>
                            <th>Klasifikasi Wilayah</th>
                            <th class="w-72">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $item)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $item->code }}</td>

                                <td>
                                    <div class="font-semibold text-slate-900">{{ $item->title }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->agency }}</div>
                                </td>

                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($item->attendeds as $att)
                                            <span class="badge badge-neutral">{{ $att->rank_abbreviation }}</span>
                                        @endforeach
                                    </div>
                                </td>

                                <td>
                                    <div>{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($item->time)->format('H:i') }} WITA
                                    </div>
                                </td>

                                <td>
                                    @if ($item->region_classification == 'dalam_daerah')
                                        <span class="badge badge-info">Dalam Daerah</span>
                                    @elseif(in_array($item->region_classification, ['luar_daerah_kabupaten', 'dalam_daerah_kabupaten']))
                                        <span class="badge badge-success">Luar Daerah Kabupaten</span>
                                    @else
                                        <span class="badge badge-danger">Luar Daerah</span>
                                    @endif
                                    <div class="mt-1 text-xs text-slate-500">{{ $item->location }}</div>
                                </td>

                                <td>
                                    <div class="grid gap-2">
                                        <a href="{{ $item->assignmentUsers->isNotEmpty()
                                            ? route('assignment-users.edit', $item->assignmentUsers->first()->id)
                                            : route('assignment-users.create', ['assignment_id' => $item->id]) }}"
                                            class="btn btn-info w-full justify-center text-xs">
                                            {{ $item->assignmentUsers->isNotEmpty() ? 'Ubah Petugas' : 'Tugaskan' }}
                                        </a>

                                        @if ($item->assignmentUsers->isNotEmpty())
                                            <a href="{{ route('assignments.print-sppd', $item->id) }}"
                                                class="btn btn-success w-full justify-center text-xs">
                                                Cetak SPT/SPPD
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-secondary w-full justify-center text-xs" disabled>
                                                Cetak SPT/SPPD
                                            </button>
                                        @endif

                                        <div class="grid grid-cols-3 gap-2">
                                            <a href="{{ route('assignments.show', $item->id) }}"
                                                class="btn btn-secondary w-full justify-center px-2 py-1.5 text-xs">
                                                Detail
                                            </a>
                                            <a href="{{ route('assignments.edit', $item->id) }}"
                                                class="btn btn-warning w-full justify-center px-2 py-1.5 text-xs">
                                                Edit
                                            </a>
                                            <form action="{{ route('assignments.destroy', $item->id) }}" method="POST"
                                                class="w-full" data-confirm="Hapus data giat ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-danger w-full justify-center px-2 py-1.5 text-xs"
                                                    data-loading-text="Menghapus...">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">Belum ada data tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
