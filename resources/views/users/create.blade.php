<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Tambah User</h2>
            <p class="text-sm text-slate-500">Tambahkan pengguna baru ke dalam sistem.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="form-grid">
                        <div>
                            <label class="field-label">Sub Bidang</label>
                            <select name="sub_division_id" required>
                                @foreach ($subDivisions as $sd)
                                    <option value="{{ $sd->id }}" {{ old('sub_division_id') == $sd->id ? 'selected' : '' }}>
                                        {{ $sd->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label class="field-label">Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div>
                            <label class="field-label">NIP</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" required>
                        </div>
                        <div>
                            <label class="field-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label class="field-label">Pangkat</label>
                            <input type="text" name="rank" value="{{ old('rank') }}" required>
                        </div>
                        <div>
                            <label class="field-label">Jabatan</label>
                            <input type="text" name="job_title" value="{{ old('job_title') }}" required>
                        </div>
                        <div>
                            <label class="field-label">Tingkat Menurut Peraturan</label>
                            <input type="text" name="assignment_regulation_level"
                                value="{{ old('assignment_regulation_level') }}">
                        </div>
                        <div>
                            <label class="field-label">Role</label>
                            <select name="role" required>
                                <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                                <option value="STAFF" {{ old('role', 'STAFF') === 'STAFF' ? 'selected' : '' }}>STAFF</option>
                                <option value="PIMPINAN ADPIM" {{ old('role') === 'PIMPINAN ADPIM' ? 'selected' : '' }}>
                                    PIMPINAN ADPIM
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-3">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active') ? 'checked' : '' }}>
                            <label for="is_active" class="text-sm font-medium text-slate-700">Status Aktif</label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Catatan</label>
                            <textarea name="note" rows="3">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
