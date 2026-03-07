<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Data Staff
                </h2>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center justify-center bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm transition">
                    + Tambah User
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-flash-alerts />

            <div class="mb-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col md:flex-row md:items-end gap-3">
                    @csrf

                    <div class="w-full md:w-auto md:min-w-[320px]">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Import User (XLSX)
                        </label>
                        <input type="file" name="file"
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                            required>
                        <p class="text-xs text-slate-500 mt-1">
                            Header wajib: sub_division,email,password,nip,name,rank,job_title,role
                        </p>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition">
                        Import XLSX
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Code</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Nama</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Role</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Tingkat Peraturan</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $user->code }}
                                    </td>

                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                        {{ $user->name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $user->email }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600 capitalize">
                                        {{ $user->role }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $user->assignment_regulation_level ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm">
                                        @if ($user->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-md">
                                                Aktif
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-md">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-md transition">
                                                Edit
                                            </a>

                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus data?')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-red-600 hover:bg-red-700 text-white rounded-md transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                        Belum ada data user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50">
                    {{ $users->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
