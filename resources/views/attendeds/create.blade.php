<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Tambah Pimpinan</h2>
            <p class="text-sm text-slate-500">Tambahkan data pimpinan yang menghadiri kegiatan.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-3xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('attendeds.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="field-label">Nama Pimpinan</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama pimpinan"
                            required>
                    </div>

                    <div>
                        <label class="field-label">Pangkat / Jabatan</label>
                        <input type="text" name="rank" value="{{ old('rank') }}"
                            placeholder="Masukkan pangkat atau jabatan" required>
                    </div>

                    <div>
                        <label class="field-label">Singkatan Pangkat</label>
                        <input type="text" name="rank_abbreviation" value="{{ old('rank_abbreviation') }}"
                            placeholder="Contoh: Kol, Let, dan seterusnya" required>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('attendeds.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
