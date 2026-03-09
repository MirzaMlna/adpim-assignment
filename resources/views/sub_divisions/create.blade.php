<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Tambah Sub Bagian</h2>
            <p class="text-sm text-slate-500">Tambahkan data sub bagian baru.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-3xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('sub-divisions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="field-label">Nama Sub Bagian</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('sub-divisions.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
