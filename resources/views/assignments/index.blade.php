<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Data Tugas
            </h2>

            <a href="{{ route('assignments.create') }}"
                class="px-4 py-2.5 rounded-lg bg-slate-800 text-white hover:bg-slate-900">
                + Tambah Tugas
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

            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Pimpinan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Total Biaya</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($assignments as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">{{ $item->code }}</td>
                                    <td class="px-4 py-3">{{ $item->title }}</td>
                                    <td class="px-4 py-3">{{ $item->attended->name }}</td>
                                    <td class="px-4 py-3">{{ $item->date }}</td>
                                    <td class="px-4 py-3">
                                        Rp {{ number_format($item->total_fee, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 flex gap-2">
                                        <a href="{{ route('assignments.edit', $item->id) }}"
                                            class="px-3 py-1 bg-amber-500 text-white rounded-md">
                                            Edit
                                        </a>

                                        <form action="{{ route('assignments.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus data?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1 bg-red-600 text-white rounded-md">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500">
                                        Belum ada data tugas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $assignments->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
