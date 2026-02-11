<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Pimpinan yang Menghadiri
                </h2>
            </div>

            <a href="{{ route('attendeds.create') }}"
                class="inline-flex items-center justify-center bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm transition">
                + Tambah Pimpinan
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">No</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Nama</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Pangkat / Jabatan</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($attendeds as $index => $item)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $attendeds->firstItem() + $index }}
                                    </td>

                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                        {{ $item->name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $item->rank }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('attendeds.edit', $item->id) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-md transition">
                                                Edit
                                            </a>

                                            <form action="{{ route('attendeds.destroy', $item->id) }}" method="POST"
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
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                        Belum ada data pimpinan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50">
                    {{ $attendeds->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
