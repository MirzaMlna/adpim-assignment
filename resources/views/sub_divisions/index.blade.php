<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Sub Bagian</h2>
            <a href="{{ route('sub-divisions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Tambah Sub Bagian
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
                            <th>Nama Sub Bagian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subDivisions as $index => $item)
                            <tr>
                                <td>{{ $subDivisions->firstItem() + $index }}</td>
                                <td class="font-semibold text-slate-900">{{ $item->name }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('sub-divisions.edit', $item->id) }}"
                                            class="btn btn-warning px-3 py-1.5 text-xs">
                                            Edit
                                        </a>
                                        <form action="{{ route('sub-divisions.destroy', $item->id) }}" method="POST"
                                            data-confirm="Yakin hapus data sub bagian ini?">
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
                                <td colspan="3" class="py-8 text-center text-slate-500">Belum ada data sub bagian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
                {{ $subDivisions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
