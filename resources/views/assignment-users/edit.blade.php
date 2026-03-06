<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Edit Assignment User
            </h2>

            <a href="{{ route('assignment-users.index') }}"
                class="px-4 py-2.5 rounded-lg bg-slate-200 text-slate-800 hover:bg-slate-300">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                                    {{ $assignment_user->user_id == $user->id ? 'selected' : '' }}>
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
                        <select id="assignment_select" name="assignment_id">
                            <option value="">-- Pilih Assignment --</option>
                            @foreach ($assignments as $assignment)
                                <option value="{{ $assignment->id }}"
                                    {{ $assignment_user->assignment_id == $assignment->id ? 'selected' : '' }}>
                                    {{ $assignment->code }} - {{ $assignment->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button class="px-6 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900">
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
