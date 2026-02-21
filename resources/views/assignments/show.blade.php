<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-800">
            Detail Penugasan
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto">

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
                        <p class="text-slate-500">Biaya per Hari</p>
                        <p class="font-medium">
                            Rp {{ number_format($assignment->fee_per_day, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-slate-500">Total Biaya</p>
                        <p class="font-semibold text-slate-800">
                            Rp {{ number_format($assignment->total_fee, 0, ',', '.') }}
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

                @if ($assignment->description)
                    <div>
                        <p class="text-slate-500 mb-2">Deskripsi</p>
                        <p class="text-sm text-slate-700">
                            {{ $assignment->description }}
                        </p>
                    </div>
                @endif

                <div class="pt-6">
                    <a href="{{ route('assignments.index') }}" class="px-4 py-2 bg-slate-800 text-white rounded-lg">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
