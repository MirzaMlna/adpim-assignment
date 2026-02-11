<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Edit User
            </h2>
            <p class="text-sm text-slate-500">
                Perbarui data pengguna sistem
            </p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Sub Bidang
                            </label>
                            <select name="sub_division_id"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                @foreach ($subDivisions as $sd)
                                    <option value="{{ $sd->id }}"
                                        {{ $user->sub_division_id == $sd->id ? 'selected' : '' }}>
                                        {{ $sd->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Email
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Password Baru
                            </label>
                            <input type="password" name="password"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                            <p class="text-xs text-slate-500 mt-1">
                                Kosongkan jika tidak ingin mengubah password
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                NIP
                            </label>
                            <input type="number" name="nip" value="{{ old('nip', $user->nip) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Nama
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Pangkat
                            </label>
                            <input type="text" name="rank" value="{{ old('rank', $user->rank) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Jabatan
                            </label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Role
                            </label>
                            <select name="role"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                <option value="ADMIN" {{ $user->role == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                                <option value="STAFF" {{ $user->role == 'STAFF' ? 'selected' : '' }}>STAFF</option>
                                <option value="PIMPINAN ADPIM" {{ $user->role == 'PIMPINAN ADPIM' ? 'selected' : '' }}>
                                    PIMPINAN ADPIM
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3">
                            <input type="checkbox" name="is_active" value="1"
                                {{ $user->is_active ? 'checked' : '' }}
                                class="rounded border-slate-300 text-slate-800 focus:ring-slate-800">
                            <label class="text-sm text-slate-700">Status Aktif</label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Catatan
                            </label>
                            <textarea name="note" rows="3"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">{{ old('note', $user->note) }}</textarea>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('users.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition">
                            Update Data
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
