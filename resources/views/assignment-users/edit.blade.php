<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-slate-900">Edit Assignment User</h2>
            <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form method="POST" action="{{ route('assignment-users.update', $assignment_user->id) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="user_ids" class="field-label">Petugas</label>
                        <select id="user_ids" name="user_id[]" multiple data-tom-select
                            data-tom-select-options='{"placeholder":"Pilih petugas...","maxItems":5}'>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ in_array($user->id, old('user_id', $assignedUserIds ?? [])) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Bisa mencari nama dan memilih lebih dari satu.</p>
                    </div>

                    <div>
                        <label class="field-label">Assignment</label>
                        <div class="panel-soft px-4 py-3 text-sm">
                            <p class="font-semibold text-slate-900">{{ $lockedAssignment?->code }}</p>
                            <p class="text-slate-600">{{ $lockedAssignment?->title }}</p>
                        </div>
                        <input type="hidden" name="assignment_id" value="{{ $assignment_user->assignment_id }}">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary" data-loading-text="Memperbarui..."
                            {{ $users->isEmpty() ? 'disabled' : '' }}>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
