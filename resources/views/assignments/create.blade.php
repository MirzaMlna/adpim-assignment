<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Tambah Giat
            </h2>
            <p class="text-sm text-slate-500">
                Tambahkan data kegiatan / penugasan baru
            </p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash-alerts />

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

                <form action="{{ route('assignments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Klasifikasi Wilayah
                            </label>
                            <select id="region_classification" name="region_classification" required
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                <option value="dalam_daerah"
                                    {{ old('region_classification', 'dalam_daerah') == 'dalam_daerah' ? 'selected' : '' }}>
                                    Dalam Daerah</option>
                                <option value="dalam_daerah_kabupaten"
                                    {{ old('region_classification') == 'dalam_daerah_kabupaten' ? 'selected' : '' }}>
                                    Dalam Daerah Kabupaten</option>
                                <option value="luar_daerah"
                                    {{ old('region_classification') == 'luar_daerah' ? 'selected' : '' }}>
                                    Luar Daerah</option>
                            </select>
                        </div>

                        <div id="wilayah_dalam_daerah" style="display:none;">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Wilayah Dalam Daerah</label>
                            <select name="location"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                <option value="Banjarmasin"
                                    {{ old('location') == 'Banjarmasin' ? 'selected' : '' }}>Kota Banjarmasin</option>
                                <option value="Banjarbaru"
                                    {{ old('location') == 'Banjarbaru' ? 'selected' : '' }}>Kota Banjarbaru</option>
                                <option value="Banjar" {{ old('location') == 'Banjar' ? 'selected' : '' }}>Kabupaten
                                    Banjar</option>
                                <option value="Barito Kuala"
                                    {{ old('location') == 'Barito Kuala' ? 'selected' : '' }}>Kabupaten Barito Kuala
                                </option>
                            </select>
                        </div>

                        <div id="wilayah_dalam_daerah_kabupaten" style="display:none;">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Wilayah Dalam Daerah
                                Kabupaten</label>
                            <select name="location"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                <option value="Hulu Sungai Selatan"
                                    {{ old('location') == 'Hulu Sungai Selatan' ? 'selected' : '' }}>Kabupaten Hulu
                                    Sungai Selatan</option>
                                <option value="Hulu Sungai Tengah"
                                    {{ old('location') == 'Hulu Sungai Tengah' ? 'selected' : '' }}>Kabupaten Hulu
                                    Sungai Tengah</option>
                                <option value="Hulu Sungai Utara"
                                    {{ old('location') == 'Hulu Sungai Utara' ? 'selected' : '' }}>Kabupaten Hulu
                                    Sungai Utara</option>
                                <option value="Balangan" {{ old('location') == 'Balangan' ? 'selected' : '' }}>
                                    Kabupaten Balangan</option>
                                <option value="Kotabaru" {{ old('location') == 'Kotabaru' ? 'selected' : '' }}>
                                    Kabupaten Kotabaru</option>
                                <option value="Tabalong" {{ old('location') == 'Tabalong' ? 'selected' : '' }}>
                                    Kabupaten Tabalong</option>
                                <option value="Tanah Laut" {{ old('location') == 'Tanah Laut' ? 'selected' : '' }}>
                                    Kabupaten Tanah Laut</option>
                                <option value="Tanah Bumbu" {{ old('location') == 'Tanah Bumbu' ? 'selected' : '' }}>
                                    Kabupaten Tanah Bumbu</option>
                                <option value="Tapin" {{ old('location') == 'Tapin' ? 'selected' : '' }}>Kabupaten
                                    Tapin</option>
                            </select>
                        </div>

                        <div id="wilayah_luar_daerah" style="display:none;">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Wilayah Luar Daerah</label>
                            <input type="text" name="location" placeholder="Nama Provinsi/Kota"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Pimpinan
                            </label>

                            <select id="attended_ids" name="attended_ids[]" multiple
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                                @foreach ($attendeds as $att)
                                    <option value="{{ $att->id }}"
                                        {{ in_array($att->id, old('attended_ids', [])) ? 'selected' : '' }}>
                                        {{ $att->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Judul Kegiatan
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Penyelenggara
                            </label>
                            <input type="text" name="agency" value="{{ old('agency') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Tanggal
                            </label>
                            <input type="date" name="date" value="{{ old('date') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Tanggal Berangkat Petugas
                            </label>
                            <input type="date" name="boarding_date" value="{{ old('boarding_date') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Jam
                            </label>
                            <input type="time" name="time" value="{{ old('time') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Transportasi
                            </label>
                            <input type="text" name="transportation" value="{{ old('transportation') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Lama Hari
                            </label>
                            <input type="number" name="day_count" value="{{ old('day_count', 1) }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                min="1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Bayaran per Hari
                            </label>
                            <input type="number" step="0.01" name="fee_per_day" value="{{ old('fee_per_day') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Detail Lokasi
                            </label>
                            <input type="text" name="location_detail" value="{{ old('location_detail') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea name="description" rows="3"
                                class="w-full rounded-lg border-slate-300 focus:border-slate-800 focus:ring-slate-800">{{ old('description') }}</textarea>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('assignments.index') }}"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition">
                            Simpan Data
                        </button>
                    </div>

                </form>
                <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
                <script>
                    const regionSelect = document.getElementById('region_classification');
                    const dalamDaerah = document.getElementById('wilayah_dalam_daerah');
                    const dalamDaerahKab = document.getElementById('wilayah_dalam_daerah_kabupaten');
                    const luarDaerah = document.getElementById('wilayah_luar_daerah');

                    const dalamSelect = dalamDaerah.querySelector('select');
                    const kabSelect = dalamDaerahKab.querySelector('select');
                    const luarInput = luarDaerah.querySelector('input');

                    function resetAll() {
                        dalamDaerah.style.display = 'none';
                        dalamDaerahKab.style.display = 'none';
                        luarDaerah.style.display = 'none';

                        dalamSelect.disabled = true;
                        kabSelect.disabled = true;
                        luarInput.disabled = true;
                    }

                    regionSelect.addEventListener('change', function() {
                        resetAll();

                        if (this.value === 'dalam_daerah') {
                            dalamDaerah.style.display = 'block';
                            dalamSelect.disabled = false;
                        } else if (this.value === 'dalam_daerah_kabupaten') {
                            dalamDaerahKab.style.display = 'block';
                            kabSelect.disabled = false;
                        } else if (this.value === 'luar_daerah') {
                            luarDaerah.style.display = 'block';
                            luarInput.disabled = false;
                        }
                    });

                    regionSelect.dispatchEvent(new Event('change'));

                    new TomSelect("#attended_ids", {
                        plugins: ['remove_button'],
                        placeholder: "Pilih pimpinan...",
                        persist: false,
                        create: false,
                    });
                </script>

            </div>
        </div>
    </div>
</x-app-layout>
