<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Edit Sub Bagian</h2>
            <p class="text-sm text-slate-500">Perbarui data sub bagian.</p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-3xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('sub-divisions.update', $subDivision->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field-label">Nama Sub Bagian</label>
                        <input type="text" name="name" value="{{ old('name', $subDivision->name) }}" required>
                        @error('name')
                            <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('sub-divisions.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Memperbarui...">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
