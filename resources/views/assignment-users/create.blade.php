<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Tambah Assignment User
            </h2>

            <a href="{{ route('assignments.index') }}"
                class="px-4 py-2.5 rounded-lg bg-slate-200 text-slate-800 hover:bg-slate-300">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto">
            <x-flash-alerts />

            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <form method="POST" action="{{ route('assignment-users.store') }}">
                    @csrf

                    {{-- PETUGAS --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Petugas
                        </label>

                        <select id="user_ids" name="user_id[]" multiple>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ collect(old('user_id'))->contains($user->id) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                        <p class="text-xs text-slate-500 mt-1">
                            Bisa mencari nama dan memilih lebih dari satu.
                        </p>
                        @if ($users->isEmpty())
                            <p class="text-xs text-red-600 mt-1">
                                Data petugas belum tersedia.
                            </p>
                        @endif
                    </div>

                    {{-- ASSIGNMENT --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Assignment
                        </label>

                        @if ($lockedAssignment)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                <p class="font-medium text-slate-800">{{ $lockedAssignment->code }}</p>
                                <p class="text-slate-600">{{ $lockedAssignment->title }}</p>
                            </div>
                            <input type="hidden" name="assignment_id"
                                value="{{ old('assignment_id', $lockedAssignment->id) }}">
                        @else
                            <select id="assignment_select" name="assignment_id">
                                <option value="">-- Pilih Assignment --</option>
                                @foreach ($assignments as $assignment)
                                    <option value="{{ $assignment->id }}"
                                        {{ old('assignment_id', request('assignment_id')) == $assignment->id ? 'selected' : '' }}>
                                        {{ $assignment->code }} - {{ $assignment->title }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($assignments->isEmpty())
                                <p class="text-xs text-red-600 mt-1">
                                    Tidak ada assignment tersedia untuk ditugaskan.
                                </p>
                            @endif
                        @endif
                    </div>

                    <!-- ...fields removed as requested... -->

                    <div class="flex justify-end">
                        <button
                            class="px-6 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 disabled:opacity-60 disabled:cursor-not-allowed"
                            {{ $users->isEmpty() || (!$lockedAssignment && $assignments->isEmpty()) ? 'disabled' : '' }}>
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- TOM SELECT --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <script>
        // PETUGAS (Multiple Select)
        new TomSelect("#user_ids", {
            plugins: ['remove_button'],
            placeholder: "Pilih petugas...",
            maxItems: 5,
            persist: false,
            create: false,
        });

        // ASSIGNMENT (Single Select dengan Search)
        if (document.querySelector("#assignment_select")) {
            new TomSelect("#assignment_select", {
                placeholder: "Cari assignment...",
                persist: false,
                create: false,
                allowEmptyOption: true,
            });
        }
    </script>

</x-app-layout>
