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

            <div class="bg-white rounded-2xl shadow-sm border">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm ">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Pimpinan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Per hari</th>
                                <th class="px-4 py-3 text-left w-40">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($assignments as $item)
                                <tr class="hover:bg-slate-50 align-middle">

                                    <td class="px-4 py-3 font-semibold text-slate-700">
                                        {{ $item->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">
                                            {{ $item->title }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $item->agency }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            @foreach ($item->attendeds as $att)
                                                <span class="inline-block bg-slate-200 px-2 py-1 rounded text-xs">
                                                    {{ $att->rank_abbreviation }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-slate-600">
                                        {{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}
                                        <br>
                                        <span class="text-xs text-slate-500">
                                            {{ \Carbon\Carbon::parse($item->time)->format('H:i') }} WITA
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-slate-700">
                                        Rp{{ number_format($item->fee_per_day, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="{{ route('assignments.show', $item->id) }}"
                                                class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs">
                                                Detail
                                            </a>

                                            <a href="{{ route('assignments.edit', $item->id) }}"
                                                class="px-3 py-1 bg-amber-500 text-white rounded-md text-xs">
                                                Edit
                                            </a>

                                            <form action="{{ route('assignments.destroy', $item->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus data?')">
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
                                    <td colspan="6" class="text-center py-6 text-slate-500">
                                        Belum ada data tugas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $assignments->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
