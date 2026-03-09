@php
    $alerts = [
        [
            'type' => 'error',
            'message' => session('error'),
            'icon' => 'bi-x-octagon-fill',
            'class' => 'flash flash-error',
        ],
        [
            'type' => 'warning',
            'message' => session('warning'),
            'icon' => 'bi-exclamation-triangle-fill',
            'class' => 'flash flash-warning',
        ],
        [
            'type' => 'info',
            'message' => session('info'),
            'icon' => 'bi-info-circle-fill',
            'class' => 'flash flash-info',
        ],
        [
            'type' => 'success',
            'message' => session('success'),
            'icon' => 'bi-check-circle-fill',
            'class' => 'flash flash-success',
        ],
    ];
@endphp

@if ($errors->any())
    <div x-data="{ open: true }" x-show="open" x-transition class="flash flash-error" role="alert" aria-live="assertive">
        <i class="bi bi-x-octagon-fill mt-0.5 text-base"></i>
        <div class="min-w-0 flex-1">
            <p class="font-semibold">Periksa kembali input berikut:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="text-current/70 hover:text-current" @click="open = false" aria-label="Tutup notifikasi error">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
@endif

@foreach ($alerts as $alert)
    @if (filled($alert['message']))
        <div x-data="{ open: true }" x-show="open" x-transition class="{{ $alert['class'] }}" role="status"
            aria-live="polite">
            <i class="bi {{ $alert['icon'] }} mt-0.5 text-base"></i>
            <p class="min-w-0 flex-1">{{ $alert['message'] }}</p>
            <button type="button" class="text-current/70 hover:text-current" @click="open = false"
                aria-label="Tutup notifikasi {{ $alert['type'] }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif
@endforeach
