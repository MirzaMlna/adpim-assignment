<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Detail Penugasan</h2>
    </x-slot>

    <div class="page-section">
        <div class="content-shell max-w-5xl">
            <x-flash-alerts />

            <div class="panel p-6 sm:p-8">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-slate-900">{{ $assignment->title }}</h3>
                    <p class="text-sm text-slate-500">{{ $assignment->code }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 text-sm md:grid-cols-2">
                    <div>
                        <p class="text-slate-500">Penyelenggara</p>
                        <p class="font-semibold text-slate-900">{{ $assignment->agency }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal dan Jam</p>
                        <p class="font-semibold text-slate-900">
                            {{ \Carbon\Carbon::parse($assignment->date)->format('d M Y') }} - {{ $assignment->time }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal Berangkat Petugas</p>
                        <p class="font-semibold text-slate-900">
                            {{ $assignment->boarding_date ? \Carbon\Carbon::parse($assignment->boarding_date)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal Pulang Petugas</p>
                        <p class="font-semibold text-slate-900">
                            {{ $assignment->return_date ? \Carbon\Carbon::parse($assignment->return_date)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Klasifikasi Wilayah</p>
                        <p class="font-semibold text-slate-900">
                            @if ($assignment->region_classification == 'dalam_daerah')
                                Dalam Daerah
                            @elseif($assignment->region_classification == 'dalam_daerah_kabupaten')
                                Dalam Daerah Kabupaten
                            @else
                                Luar Daerah
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Lokasi</p>
                        <p class="font-semibold text-slate-900">{{ $assignment->location }}</p>
                        <p class="text-xs text-slate-500">{{ $assignment->location_detail ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Durasi</p>
                        <p class="font-semibold text-slate-900">{{ $assignment->day_count }} Hari</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Transportasi</p>
                        <p class="font-semibold text-slate-900">{{ $assignment->transportation ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Biaya per Hari</p>
                        <p class="font-semibold text-slate-900">
                            Rp {{ number_format($assignment->fee_per_day, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="mb-2 text-sm text-slate-500">Pimpinan yang Hadir</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($assignment->attendeds as $att)
                            <span class="badge badge-neutral">{{ $att->rank_abbreviation }} - {{ $att->name }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <p class="mb-2 text-sm text-slate-500">Petugas Ditugaskan</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($assignment->assignmentUsers as $item)
                            <span class="badge badge-neutral">{{ $item->user->name }}</span>
                        @empty
                            <span class="text-sm text-amber-700">Belum ada petugas yang ditugaskan.</span>
                        @endforelse
                    </div>
                </div>

                @if ($assignment->description)
                    <div class="mt-6">
                        <p class="mb-2 text-sm text-slate-500">Deskripsi</p>
                        <p class="text-sm text-slate-700">{{ $assignment->description }}</p>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-2">
                    <a href="{{ $assignment->assignmentUsers->isNotEmpty()
                        ? route('assignment-users.edit', $assignment->assignmentUsers->first()->id)
                        : route('assignment-users.create', ['assignment_id' => $assignment->id]) }}"
                        class="btn btn-info">
                        {{ $assignment->assignmentUsers->isNotEmpty() ? 'Ubah Petugas' : 'Tugaskan Petugas' }}
                    </a>
                    @if ($assignment->assignmentUsers->isNotEmpty())
                        <a href="{{ route('assignments.print-sppd', $assignment->id) }}" class="btn btn-success">
                            Cetak SPT/SPPD
                        </a>
                    @endif
                    <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
