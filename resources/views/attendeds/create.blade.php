<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Tambah Pimpinan
            </h2>
            <p class="text-sm text-slate-500">
                Tambahkan data pimpinan yang menghadiri kegiatan
            </p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

                <form action="{{ route('attendeds.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Nama Pimpinan
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                            placeholder="Masukkan nama pimpinan" required>

                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Pangkat / Jabatan
                        </label>
                        <input type="text" name="rank" value="{{ old('rank') }}"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                            placeholder="Masukkan pangkat atau jabatan" required>

                        @error('rank')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Singkatan Pangkat
                        </label>
                        <input type="text" name="rank_abbreviation" value="{{ old('rank_abbreviation') }}"
                            class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                            placeholder="Masukkan singkatan pangkat (contoh: Kol, Let, etc.)" required>

                        @error('rank_abbreviation')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('attendeds.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition">
                            Simpan Data
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
