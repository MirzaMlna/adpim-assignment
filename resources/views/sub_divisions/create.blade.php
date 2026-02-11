<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Sub Bidang
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('sub-divisions.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-2">Nama Sub Bidang</label>
                        <input type="text" name="name" class="w-full border rounded px-3 py-2"
                            value="{{ old('name') }}" required>

                        @error('name')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded">
                        Simpan
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
