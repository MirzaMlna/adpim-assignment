<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-800">
            Detail Penugasan
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto">
            <x-flash-alerts />

            <div class="bg-white rounded-2xl shadow-sm border p-8 space-y-6">

                <div>
                    <h3 class="text-lg font-semibold text-slate-800">
                        {{ $assignment->title }}
                    </h3>
                    <p class="text-sm text-slate-500">
                        {{ $assignment->code }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm">

                    <div>
                        <p class="text-slate-500">Penyelenggara</p>
                        <p class="font-medium">{{ $assignment->agency }}</p>
                    </div>

                    <div>
                        <p class="text-slate-500">Tanggal & Jam</p>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($assignment->date)->format('d M Y') }}
                            - {{ $assignment->time }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Tanggal Berangkat Petugas</p>
                        <p class="font-medium">
                            {{ $assignment->boarding_date ? \Carbon\Carbon::parse($assignment->boarding_date)->format('d M Y') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Tanggal Pulang Petugas</p>
                        <p class="font-medium">
                            {{ $assignment->return_date ? \Carbon\Carbon::parse($assignment->return_date)->format('d M Y') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Klasifikasi Wilayah</p>
                        <p class="font-medium">
                            @if ($assignment->region_classification == 'dalam_daerah')
                                Dalam Daerah (Banjarmasin, Banjarbaru, Banjar, Barito Kuala)
                            @elseif($assignment->region_classification == 'dalam_daerah_kabupaten')
                                Dalam Daerah Kabupaten (HSS, HST, HSU, BLG, KTB, TANBU, TALA, TAPIN, TBL)
                            @else
                                Luar Daerah (Provinsi di luar Kalimantan Selatan)
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Lokasi</p>
                        <p class="font-medium">
                            {{ $assignment->location }}
                            <br>
                            <span class="text-xs text-slate-500">
                                {{ $assignment->location_detail }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Durasi</p>
                        <p class="font-medium">
                            {{ $assignment->day_count }} Hari
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Transportasi</p>
                        <p class="font-medium">
                            {{ $assignment->transportation ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Biaya per Hari</p>
                        <p class="font-medium">
                            Rp {{ number_format($assignment->fee_per_day, 0, ',', '.') }}
                        </p>
                    </div>

                </div>

                <div>
                    <p class="text-slate-500 mb-2">Pimpinan yang Hadir</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($assignment->attendeds as $att)
                            <span class="bg-slate-200 px-3 py-1 rounded text-sm">
                                {{ $att->rank_abbreviation }} - {{ $att->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-slate-500 mb-2">Petugas Ditugaskan</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($assignment->assignmentUsers as $item)
                            <span class="bg-slate-200 px-3 py-1 rounded text-sm">
                                {{ $item->user->name }}
                            </span>
                        @empty
                            <span class="text-sm text-amber-600">
                                Belum ada petugas yang ditugaskan.
                            </span>
                        @endforelse
                    </div>
                </div>

                @if ($assignment->description)
                    <div>
                        <p class="text-slate-500 mb-2">Deskripsi</p>
                        <p class="text-sm text-slate-700">
                            {{ $assignment->description }}
                        </p>
                    </div>
                @endif

                <div class="pt-6">
                    <a href="{{ $assignment->assignmentUsers->isNotEmpty()
                        ? route('assignment-users.edit', $assignment->assignmentUsers->first()->id)
                        : route('assignment-users.create', ['assignment_id' => $assignment->id]) }}"
                        class="px-4 py-2 bg-sky-600 text-white rounded-lg mr-2">
                        {{ $assignment->assignmentUsers->isNotEmpty() ? 'Ubah Petugas' : 'Tugaskan Petugas' }}
                    </a>
                    @if ($assignment->assignmentUsers->isNotEmpty())
                        <a href="{{ route('assignments.print-sppd', $assignment->id) }}"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg mr-2">
                            Cetak SPPD
                        </a>
                    @endif
                    <a href="{{ route('assignments.index') }}" class="px-4 py-2 bg-slate-800 text-white rounded-lg">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
