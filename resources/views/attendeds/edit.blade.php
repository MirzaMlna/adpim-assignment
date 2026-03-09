<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Edit Pimpinan</h2>
            <p class="text-sm text-slate-500">Perbarui data pimpinan yang menghadiri kegiatan.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-3xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('attendeds.update', $attended->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field-label">Nama Pimpinan</label>
                        <input type="text" name="name" value="{{ old('name', $attended->name) }}" required>
                    </div>

                    <div>
                        <label class="field-label">Pangkat / Jabatan</label>
                        <input type="text" name="rank" value="{{ old('rank', $attended->rank) }}" required>
                    </div>

                    <div>
                        <label class="field-label">Singkatan Pangkat</label>
                        <input type="text" name="rank_abbreviation"
                            value="{{ old('rank_abbreviation', $attended->rank_abbreviation) }}" required>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('attendeds.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Memperbarui...">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
