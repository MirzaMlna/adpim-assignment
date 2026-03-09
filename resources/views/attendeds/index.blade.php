<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Pimpinan yang Menghadiri</h2>
            <a href="{{ route('attendeds.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Tambah Pimpinan
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
                            <th>No</th>
                            <th>Nama</th>
                            <th>Pangkat / Jabatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendeds as $index => $item)
                            <tr>
                                <td>{{ $attendeds->firstItem() + $index }}</td>
                                <td class="font-semibold text-slate-900">{{ $item->name }}</td>
                                <td>{{ $item->rank }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('attendeds.edit', $item->id) }}"
                                            class="btn btn-warning px-3 py-1.5 text-xs">
                                            Edit
                                        </a>

                                        <form action="{{ route('attendeds.destroy', $item->id) }}" method="POST"
                                            data-confirm="Yakin hapus data pimpinan ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 py-1.5 text-xs"
                                                data-loading-text="Menghapus...">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">Belum ada data pimpinan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
                {{ $attendeds->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
