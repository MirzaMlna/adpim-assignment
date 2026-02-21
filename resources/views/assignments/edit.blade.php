<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Edit Tugas
            </h2>
            <p class="text-sm text-slate-500">
                Perbarui data kegiatan / penugasan
            </p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

                <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Pimpinan
                            </label>
                            <select name="attended_ids[]" multiple required
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                @foreach ($attendeds as $att)
                                    <option value="{{ $att->id }}">
                                        {{ $att->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Judul Kegiatan
                            </label>
                            <input type="text" name="title" value="{{ old('title', $assignment->title) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Penyelenggara
                            </label>
                            <input type="text" name="agency" value="{{ old('agency', $assignment->agency) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Tanggal
                            </label>
                            <input type="date" name="date" value="{{ old('date', $assignment->date) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Jam
                            </label>
                            <input type="time" name="time" value="{{ old('time', $assignment->time) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Lama Hari
                            </label>
                            <input type="number" name="day_count"
                                value="{{ old('day_count', $assignment->day_count) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Bayaran per Hari
                            </label>
                            <input type="number" step="0.01" name="fee_per_day"
                                value="{{ old('fee_per_day', $assignment->fee_per_day) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Lokasi (Kab/Kota)
                            </label>
                            <select name="location"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">

                                <option value="{{ old('location', $assignment->location) }}">
                                    {{ old('location', $assignment->location) }}</option>
                                <option value="Banjarmasin">Kota Banjarmasin</option>
                                <option value="Banjarbaru">Kota Banjarbaru</option>
                                <option value="Banjar">Kabupaten Banjar</option>
                                <option value="Barito Kuala">Kabupaten Barito Kuala</option>
                                <option value="Tapin">Kabupaten Tapin</option>
                                <option value="Hulu Sungai Selatan">Kabupaten Hulu Sungai Selatan</option>
                                <option value="Hulu Sungai Tengah">Kabupaten Hulu Sungai Tengah</option>
                                <option value="Hulu Sungai Utara">Kabupaten Hulu Sungai Utara</option>
                                <option value="Tabalong">Kabupaten Tabalong</option>
                                <option value="Tanah Laut">Kabupaten Tanah Laut</option>
                                <option value="Tanah Bumbu">Kabupaten Tanah Bumbu</option>
                                <option value="Kotabaru">Kabupaten Kotabaru</option>
                                <option value="Balangan">Kabupaten Balangan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Detail Lokasi
                            </label>
                            <input type="text" name="location_detail"
                                value="{{ old('location_detail', $assignment->location_detail) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea name="description" rows="3"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">{{ old('description', $assignment->description) }}</textarea>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('assignments.index') }}"
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
