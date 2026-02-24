<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Assignment Users
            </h2>

            <a href="{{ route('assignment-users.create') }}"
                class="px-4 py-2.5 rounded-lg bg-slate-800 text-white hover:bg-slate-900">
                + Tambah Data
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto">

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">User</th>
                                <th class="px-4 py-3 text-left">Assignment</th>
                                <th class="px-4 py-3 text-left">Lokasi</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left w-40">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($data as $row)
                                <tr class="hover:bg-slate-50 align-middle">

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">
                                            {{ $row->user->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $row->user->email }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $row->assignment->title ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-600">
                                        <div class="text-xs text-slate-500">Berangkat</div>
                                        <div class="font-medium">
                                            {{ $row->departure_location }}
                                        </div>

                                        <div class="text-xs text-slate-500 mt-1">Tujuan</div>
                                        <div class="font-medium">
                                            {{ $row->destination_location }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($row->is_verified)
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                Verified
                                            </span>
                                        @else
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                                Belum Verified
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">

                                            <a href="{{ route('assignment-users.edit', $row->id) }}"
                                                class="px-3 py-1 bg-amber-500 text-white rounded-md text-xs">
                                                Edit
                                            </a>

                                            <form action="{{ route('assignment-users.destroy', $row->id) }}"
                                                method="POST" onsubmit="return confirm('Hapus data?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1 bg-red-600 text-white rounded-md text-xs">
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500">
                                        Belum ada data assignment user
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $data->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
