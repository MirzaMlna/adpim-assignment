<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Data Assignment User</h2>
            <a href="{{ route('assignment-users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Tambah Data
            </a>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell">
            <x-flash-alerts />

            <x-month-date-filter :month="$filters['month']" :date="$filters['date']" title="Filter Penugasan"
                description="Tampilkan penugasan berdasarkan bulan, lalu sempitkan lagi ke tanggal giat tertentu." />

            <div class="table-shell">
                <table class="table-ui">
                    <thead>
                        <tr>
                            <th>Kode Tugas</th>
                            <th>Judul</th>
                            <th>Petugas</th>
                            <th>Pimpinan</th>
                            <th class="w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            @php
                                $assignmentUser = $assignment->assignmentUsers->first();
                            @endphp
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $assignment->code }}</td>
                                <td>{{ $assignment->title }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($assignment->assignmentUsers as $item)
                                            <span class="badge badge-neutral">{{ $item->user->name }}</span>
                                        @empty
                                            <span class="text-xs text-amber-700">Belum ditugaskan</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($assignment->attendeds as $att)
                                            <span class="badge badge-neutral">{{ $att->rank_abbreviation }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-2">
                                        @if ($assignmentUser)
                                            <a href="{{ route('assignments.print-sppd', $assignment->id) }}" class="btn btn-success w-full text-xs">
                                                Cetak SPT/SPPD
                                            </a>
                                        @endif

                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('assignments.show', $assignment->id) }}"
                                                class="btn btn-secondary px-3 py-1.5 text-xs">
                                                Detail
                                            </a>

                                            @if ($assignmentUser)
                                                <a href="{{ route('assignment-users.edit', $assignmentUser->id) }}"
                                                    class="btn btn-warning px-3 py-1.5 text-xs">
                                                    Edit
                                                </a>

                                                <form action="{{ route('assignment-users.destroy', $assignmentUser->id) }}"
                                                    method="POST" data-confirm="Hapus data penugasan ini?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger px-3 py-1.5 text-xs"
                                                        data-loading-text="Menghapus...">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('assignment-users.create', ['assignment_id' => $assignment->id]) }}"
                                                    class="btn btn-info px-3 py-1.5 text-xs">
                                                    Tugaskan
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">Belum ada data.</td>
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
