<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Tambah Assignment User</h2>
            <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form method="POST" action="{{ route('assignment-users.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="user_ids" class="field-label">Petugas</label>
                        <select id="user_ids" name="user_id[]" multiple data-tom-select
                            data-tom-select-options='{"placeholder":"Pilih petugas...","maxItems":5}'>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ collect(old('user_id'))->contains($user->id) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Bisa mencari nama dan memilih lebih dari satu.</p>
                        @if ($users->isEmpty())
                            <p class="mt-1 text-xs text-rose-600">Data petugas belum tersedia.</p>
                        @endif
                    </div>

                    <div>
                        <label for="assignment_select" class="field-label">Assignment</label>
                        @if ($lockedAssignment)
                            <div class="panel-soft px-4 py-3 text-sm">
                                <p class="font-semibold text-slate-900">{{ $lockedAssignment->code }}</p>
                                <p class="text-slate-600">{{ $lockedAssignment->title }}</p>
                            </div>
                            <input type="hidden" name="assignment_id"
                                value="{{ old('assignment_id', $lockedAssignment->id) }}">
                        @else
                            <select id="assignment_select" name="assignment_id" data-tom-select
                                data-tom-select-options='{"placeholder":"Cari assignment...","allowEmptyOption":true}'>
                                <option value="">-- Pilih Assignment --</option>
                                @foreach ($assignments as $assignment)
                                    <option value="{{ $assignment->id }}"
                                        {{ old('assignment_id', request('assignment_id')) == $assignment->id ? 'selected' : '' }}>
                                        {{ $assignment->code }} - {{ $assignment->title }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($assignments->isEmpty())
                                <p class="mt-1 text-xs text-rose-600">Tidak ada assignment tersedia untuk ditugaskan.</p>
                            @endif
                        @endif
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan..."
                            {{ $users->isEmpty() || (!$lockedAssignment && $assignments->isEmpty()) ? 'disabled' : '' }}>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
