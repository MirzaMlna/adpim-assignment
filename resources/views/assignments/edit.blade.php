<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                Edit Giat
            </h2>
            <p class="text-sm text-slate-500">
                Perbarui data kegiatan atau penugasan.
            </p>
        </div>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-4xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" class="space-y-6"
                    data-region-form>
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div>
                            <label for="region_classification" class="field-label">Klasifikasi Wilayah</label>
                            <select id="region_classification" name="region_classification" required data-region-select>
                                <option value="dalam_daerah"
                                    {{ old('region_classification', $assignment->region_classification) == 'dalam_daerah' ? 'selected' : '' }}>
                                    Dalam Daerah
                                </option>
                                <option value="dalam_daerah_kabupaten"
                                    {{ old('region_classification', $assignment->region_classification) == 'dalam_daerah_kabupaten' ? 'selected' : '' }}>
                                    Dalam Daerah Kabupaten
                                </option>
                                <option value="luar_daerah"
                                    {{ old('region_classification', $assignment->region_classification) == 'luar_daerah' ? 'selected' : '' }}>
                                    Luar Daerah
                                </option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="dalam_daerah">
                            <label class="field-label">Wilayah Dalam Daerah</label>
                            <select name="location">
                                <option value="Banjarmasin"
                                    {{ old('location', $assignment->location) == 'Banjarmasin' ? 'selected' : '' }}>Kota Banjarmasin</option>
                                <option value="Banjarbaru"
                                    {{ old('location', $assignment->location) == 'Banjarbaru' ? 'selected' : '' }}>Kota Banjarbaru</option>
                                <option value="Banjar"
                                    {{ old('location', $assignment->location) == 'Banjar' ? 'selected' : '' }}>Kabupaten Banjar</option>
                                <option value="Barito Kuala"
                                    {{ old('location', $assignment->location) == 'Barito Kuala' ? 'selected' : '' }}>Kabupaten Barito Kuala</option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="dalam_daerah_kabupaten">
                            <label class="field-label">Wilayah Dalam Daerah Kabupaten</label>
                            <select name="location">
                                <option value="Hulu Sungai Selatan"
                                    {{ old('location', $assignment->location) == 'Hulu Sungai Selatan' ? 'selected' : '' }}>Kabupaten Hulu Sungai Selatan</option>
                                <option value="Hulu Sungai Tengah"
                                    {{ old('location', $assignment->location) == 'Hulu Sungai Tengah' ? 'selected' : '' }}>Kabupaten Hulu Sungai Tengah</option>
                                <option value="Hulu Sungai Utara"
                                    {{ old('location', $assignment->location) == 'Hulu Sungai Utara' ? 'selected' : '' }}>Kabupaten Hulu Sungai Utara</option>
                                <option value="Balangan"
                                    {{ old('location', $assignment->location) == 'Balangan' ? 'selected' : '' }}>Kabupaten Balangan</option>
                                <option value="Kotabaru"
                                    {{ old('location', $assignment->location) == 'Kotabaru' ? 'selected' : '' }}>Kabupaten Kotabaru</option>
                                <option value="Tabalong"
                                    {{ old('location', $assignment->location) == 'Tabalong' ? 'selected' : '' }}>Kabupaten Tabalong</option>
                                <option value="Tanah Laut"
                                    {{ old('location', $assignment->location) == 'Tanah Laut' ? 'selected' : '' }}>Kabupaten Tanah Laut</option>
                                <option value="Tanah Bumbu"
                                    {{ old('location', $assignment->location) == 'Tanah Bumbu' ? 'selected' : '' }}>Kabupaten Tanah Bumbu</option>
                                <option value="Tapin"
                                    {{ old('location', $assignment->location) == 'Tapin' ? 'selected' : '' }}>Kabupaten Tapin</option>
                            </select>
                        </div>

                        <div class="hidden" data-region-option="luar_daerah">
                            <label class="field-label">Wilayah Luar Daerah</label>
                            <input type="text" name="location" value="{{ old('location', $assignment->location) }}"
                                placeholder="Nama Provinsi/Kota">
                        </div>

                        <div>
                            <label for="attended_ids" class="field-label">Pimpinan</label>
                            <select id="attended_ids" name="attended_ids[]" multiple data-tom-select
                                data-tom-select-options='{"placeholder":"Pilih pimpinan..."}'>
                                @foreach ($attendeds as $att)
                                    <option value="{{ $att->id }}"
                                        {{ in_array($att->id, old('attended_ids', $assignment->attendeds->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $att->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="field-label">Judul Kegiatan</label>
                            <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required>
                        </div>

                        <div>
                            <label class="field-label">Penyelenggara</label>
                            <input type="text" name="agency" value="{{ old('agency', $assignment->agency) }}" required>
                        </div>

                        <div>
                            <label class="field-label">Tanggal</label>
                            <input type="date" name="date" value="{{ old('date', optional($assignment->date)->format('Y-m-d')) }}"
                                required>
                        </div>

                        <div>
                            <label class="field-label">Tanggal Berangkat Petugas</label>
                            <input type="date" name="boarding_date"
                                value="{{ old('boarding_date', optional($assignment->boarding_date)->format('Y-m-d')) }}"
                                required>
                        </div>

                        <div>
                            <label class="field-label">Jam</label>
                            <input type="time" name="time" value="{{ old('time', $assignment->time) }}" required>
                        </div>

                        <div>
                            <label class="field-label">Transportasi</label>
                            <input type="text" name="transportation"
                                value="{{ old('transportation', $assignment->transportation) }}" required>
                        </div>

                        <div>
                            <label class="field-label">Lama Hari</label>
                            <input type="number" name="day_count" value="{{ old('day_count', $assignment->day_count) }}"
                                min="1" required>
                        </div>

                        <div>
                            <label class="field-label">Bayaran per Hari</label>
                            <input type="number" name="fee_per_day"
                                value="{{ old('fee_per_day', $assignment->fee_per_day) }}" step="0.01" min="0"
                                required>
                        </div>

                        <div>
                            <label class="field-label">Detail Lokasi</label>
                            <input type="text" name="location_detail"
                                value="{{ old('location_detail', $assignment->location_detail) }}">
                        </div>

                        <div class="md:col-span-2">
                            <label class="field-label">Deskripsi</label>
                            <textarea name="description" rows="3">{{ old('description', $assignment->description) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" data-loading-text="Memperbarui...">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
