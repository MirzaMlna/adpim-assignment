<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Data Staff</h2>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i>
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell">
            <x-flash-alerts />

            <div class="panel mb-4 p-4 sm:p-5">
                <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col gap-3 md:flex-row md:items-end">
                    @csrf
                    <div class="w-full md:min-w-[360px]">
                        <label class="field-label">Import User (XLSX)</label>
                        <input type="file" name="file"
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        <p class="mt-1 text-xs text-slate-500">
                            Header wajib: sub_division,email,password,nip,name,rank,job_title,role
                        </p>
                    </div>
                    <button type="submit" class="btn btn-success" data-loading-text="Mengimpor...">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        Import XLSX
                    </button>
                </form>
            </div>

            <div class="table-shell">
                <table class="table-ui">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tingkat Peraturan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-slate-600">{{ $user->code }}</td>
                                <td class="font-semibold text-slate-900">{{ $user->name }}</td>
                                <td class="text-slate-600">{{ $user->email }}</td>
                                <td class="text-slate-600">{{ $user->role }}</td>
                                <td class="text-slate-600">{{ $user->assignment_regulation_level ?? '-' }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning px-3 py-1.5 text-xs">
                                            Edit
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            data-confirm="Yakin hapus data user ini?">
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
                                <td colspan="7" class="py-8 text-center text-slate-500">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
