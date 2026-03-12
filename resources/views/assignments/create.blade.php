<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                Tambah Giat
            </h2>
            <p class="text-sm text-slate-500">
                Tambahkan data kegiatan atau penugasan baru.
            </p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('assignments.store') }}" method="POST" class="space-y-6" data-region-form>
                    @csrf

                    <div class="form-grid">
                        <div>
                            <label for="region_classification" class="field-label">Klasifikasi Wilayah</label>
                            <select id="region_classification" name="region_classification" required data-region-select>
                                <option value="dalam_daerah"
                                    {{ old('region_classification', 'dalam_daerah') == 'dalam_daerah' ? 'selected' : '' }}>
                                    Dalam Daerah
                                </option>
                                <option value="luar_daerah_kabupaten"
                                    {{ old('region_classification') == 'luar_daerah_kabupaten' ? 'selected' : '' }}>
                                    Luar Daerah Kabupaten
                                </option>
                                <option value="luar_daerah"
                                    {{ old('region_classification') == 'luar_daerah' ? 'selected' : '' }}>
                                    Luar Daerah
                                </option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="dalam_daerah">
                            <label class="field-label">Wilayah Dalam Daerah</label>
                            <select name="location">
                                <option value="Kota Banjarmasin"
                                    {{ old('location') == 'Kota Banjarmasin' ? 'selected' : '' }}>Kota Banjarmasin
                                </option>
                                <option value="Kota Banjarbaru"
                                    {{ old('location') == 'Kota Banjarbaru' ? 'selected' : '' }}>Kota Banjarbaru
                                </option>
                                <option value="Kab. Banjar" {{ old('location') == 'Kab. Banjar' ? 'selected' : '' }}>
                                    Kab. Banjar</option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="luar_daerah_kabupaten">
                            <label class="field-label">Wilayah Luar Daerah Kabupaten</label>
                            <select name="location">
                                <option value="Kab. Barito Kuala"
                                    {{ old('location') == 'Kab. Barito Kuala' ? 'selected' : '' }}>Kab. Barito Kuala
                                </option>
                                <option value="Kab. Hulu Sungai Selatan"
                                    {{ old('location') == 'Kab. Hulu Sungai Selatan' ? 'selected' : '' }}>Kab. Hulu
                                    Sungai Selatan</option>
                                <option value="Kab. Hulu Sungai Tengah"
                                    {{ old('location') == 'Kab. Hulu Sungai Tengah' ? 'selected' : '' }}>Kab. Hulu
                                    Sungai Tengah</option>
                                <option value="Kab. Hulu Sungai Utara"
                                    {{ old('location') == 'Kab. Hulu Sungai Utara' ? 'selected' : '' }}>Kab. Hulu
                                    Sungai Utara</option>
                                <option value="Kab. Balangan"
                                    {{ old('location') == 'Kab. Balangan' ? 'selected' : '' }}>Kab. Balangan</option>
                                <option value="Kab. Kotabaru"
                                    {{ old('location') == 'Kab. Kotabaru' ? 'selected' : '' }}>Kab. Kotabaru</option>
                                <option value="Kab. Tabalong"
                                    {{ old('location') == 'Kab. Tabalong' ? 'selected' : '' }}>Kab. Tabalong</option>
                                <option value="Kab. Tanah Laut"
                                    {{ old('location') == 'Kab. Tanah Laut' ? 'selected' : '' }}>Kab. Tanah Laut
                                </option>
                                <option value="Kab. Tanah Bumbu"
                                    {{ old('location') == 'Kab. Tanah Bumbu' ? 'selected' : '' }}>Kab. Tanah Bumbu
                                </option>
                                <option value="Kab. Tapin" {{ old('location') == 'Kab. Tapin' ? 'selected' : '' }}>Kab.
                                    Tapin</option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="luar_daerah">
                            <label class="field-label">Wilayah Luar Daerah</label>
                            <input type="text" name="location" value="{{ old('location') }}"
                                placeholder="Nama Provinsi/Kota">
                        </div>

                        <div>
                            <label for="attended_ids" class="field-label">Pimpinan</label>
                            <select id="attended_ids" name="attended_ids[]" multiple data-tom-select
                                data-tom-select-options='{"placeholder":"Pilih pimpinan..."}'>
                                @foreach ($attendeds as $att)
                                    <option value="{{ $att->id }}"
                                        {{ in_array($att->id, old('attended_ids', [])) ? 'selected' : '' }}>
                                        {{ $att->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Judul Kegiatan</label>
                            <input type="text" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Penyelenggara</label>
                            <input type="text" name="agency" value="{{ old('agency') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Tanggal</label>
                            <input type="date" name="date" value="{{ old('date') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Tanggal Berangkat Petugas</label>
                            <input type="date" name="boarding_date" value="{{ old('boarding_date') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Jam</label>
                            <input type="time" name="time" value="{{ old('time') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Transportasi</label>
                            <input type="text" name="transportation" value="{{ old('transportation') }}" required>
                        </div>

                        <div>
                            <label class="field-label">Lama Hari</label>
                            <input type="number" name="day_count" value="{{ old('day_count', 1) }}" min="1"
                                required>
                        </div>

                        <div>
                            <label class="field-label">Bayaran per Hari</label>
                            <input type="number" name="fee_per_day" value="{{ old('fee_per_day') }}" step="0.01"
                                min="0" required>
                        </div>

                        <div>
                            <label class="field-label">Detail Lokasi</label>
                            <input type="text" name="location_detail" value="{{ old('location_detail') }}">
                        </div>

                        <div class="md:col-span-2">
                            <label class="field-label">Deskripsi</label>
                            <textarea name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan
                            Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
