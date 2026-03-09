<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Edit User</h2>
            <p class="text-sm text-slate-500">Perbarui data pengguna sistem.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div>
                            <label class="field-label">Sub Bidang</label>
                            <select name="sub_division_id" required>
                                @foreach ($subDivisions as $sd)
                                    <option value="{{ $sd->id }}"
                                        {{ old('sub_division_id', $user->sub_division_id) == $sd->id ? 'selected' : '' }}>
                                        {{ $sd->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div>
                            <label class="field-label">Password Baru</label>
                            <input type="password" name="password">
                            <p class="mt-1 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                        </div>
                        <div>
                            <label class="field-label">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" required>
                        </div>
                        <div>
                            <label class="field-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div>
                            <label class="field-label">Pangkat</label>
                            <input type="text" name="rank" value="{{ old('rank', $user->rank) }}" required>
                        </div>
                        <div>
                            <label class="field-label">Jabatan</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" required>
                        </div>
                        <div>
                            <label class="field-label">Tingkat Menurut Peraturan</label>
                            <input type="text" name="assignment_regulation_level"
                                value="{{ old('assignment_regulation_level', $user->assignment_regulation_level) }}">
                        </div>
                        <div>
                            <label class="field-label">Role</label>
                            <select name="role" required>
                                <option value="ADMIN" {{ old('role', $user->role) === 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                                <option value="STAFF" {{ old('role', $user->role) === 'STAFF' ? 'selected' : '' }}>STAFF</option>
                                <option value="PIMPINAN ADPIM" {{ old('role', $user->role) === 'PIMPINAN ADPIM' ? 'selected' : '' }}>
                                    PIMPINAN ADPIM
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-3">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="text-sm font-medium text-slate-700">Status Aktif</label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Catatan</label>
                            <textarea name="note" rows="3">{{ old('note', $user->note) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Memperbarui...">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
