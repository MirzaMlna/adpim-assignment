<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Edit Assignment User
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

                <form method="POST" action="{{ route('assignment-users.update', $assignment_user->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- PETUGAS --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Petugas
                        </label>
                        <select id="user_ids" name="user_id[]" multiple>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ in_array($user->id, old('user_id', $assignedUserIds ?? [])) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">
                            Bisa mencari nama dan memilih lebih dari satu.
                        </p>
                    </div>

                    {{-- ASSIGNMENT --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Assignment
                        </label>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                            <p class="font-medium text-slate-800">{{ $lockedAssignment?->code }}</p>
                            <p class="text-slate-600">{{ $lockedAssignment?->title }}</p>
                        </div>
                        <input type="hidden" name="assignment_id" value="{{ $assignment_user->assignment_id }}">
                    </div>

                    <div class="flex justify-end">
                        <button
                            class="px-6 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 disabled:opacity-60 disabled:cursor-not-allowed"
                            {{ $users->isEmpty() ? 'disabled' : '' }}>
                            Update
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
        new TomSelect("#user_ids", {
            plugins: ['remove_button'],
            placeholder: "Pilih petugas...",
            maxItems: 5,
            persist: false,
            create: false,
        });
    </script>
</x-app-layout>
