<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Tambah Assignment User
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

                <form method="POST" action="{{ route('assignment-users.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            User (1 s.d 5 orang)
                        </label>
                        <select name="user_id[]" multiple size="5"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ (collect(old('user_id'))->contains($user->id)) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-xs text-slate-500 mt-1">Tekan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu user.</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Assignment
                        </label>
                        <select name="assignment_id"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                            <option value="">-- Pilih Assignment --</option>
                            @foreach ($assignments as $assignment)
                                <option value="{{ $assignment->id }}"
                                    {{ old('assignment_id') == $assignment->id ? 'selected' : '' }}>
                                    {{ $assignment->code }} - {{ $assignment->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Lokasi Berangkat
                        </label>
                        <input type="text" name="departure_location" value="{{ old('departure_location') }}"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Lokasi Tujuan
                        </label>
                        <input type="text" name="destination_location" value="{{ old('destination_location') }}"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                    </div>

                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" name="is_verified" value="1"
                            class="rounded border-slate-300 text-slate-800 focus:ring-slate-800"
                            {{ old('is_verified') ? 'checked' : '' }}>
                        <label class="text-sm text-slate-700">
                            Verified
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button class="px-6 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
