<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Data Assignment User
            </h2>

            <a href="{{ route('assignment-users.create') }}"
                class="px-4 py-2.5 rounded-lg bg-slate-800 text-white hover:bg-slate-900">
                + Tambah Data
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto">
            <x-flash-alerts />

            <div class="bg-white rounded-2xl shadow-sm border">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode Tugas</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Petugas</th>
                                <th class="px-4 py-3 text-center">Pimpinan</th>
                                <th class="px-4 py-3 text-center w-40">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse ($assignments as $assignment)
                                <tr class="hover:bg-slate-50">
                                    @php
                                        $assignmentUser = $assignment->assignmentUsers->first();
                                    @endphp

                                    <td class="px-4 py-3 font-semibold">
                                        {{ $assignment->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $assignment->title }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            @forelse ($assignment->assignmentUsers as $item)
                                                <span class="bg-slate-200 px-2 py-1 rounded text-xs inline-block">
                                                    {{ $item->user->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-amber-600">
                                                    Belum ditugaskan
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col gap-1">
                                            @foreach ($assignment->attendeds as $att)
                                                <span class="inline-block bg-slate-200 px-2 py-1 rounded text-xs">
                                                    {{ $att->rank_abbreviation }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('assignments.show', $assignment->id) }}"
                                                class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs">
                                                Detail
                                            </a>

                                            @if ($assignmentUser)
                                                <a href="{{ route('assignment-users.edit', $assignmentUser->id) }}"
                                                    class="px-3 py-1 bg-amber-500 text-white rounded-md text-xs">
                                                    Edit
                                                </a>
                                            @else
                                                <a href="{{ route('assignment-users.create', ['assignment_id' => $assignment->id]) }}"
                                                    class="px-3 py-1 bg-sky-600 text-white rounded-md text-xs">
                                                    Tugaskan
                                                </a>
                                            @endif

                                            @if ($assignmentUser)
                                                <form action="{{ route('assignment-users.destroy', $assignmentUser->id) }}"
                                                    method="POST" onsubmit="return confirm('Hapus data?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="px-3 py-1 bg-red-600 text-white rounded-md text-xs">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500">
                                        Belum ada data
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
