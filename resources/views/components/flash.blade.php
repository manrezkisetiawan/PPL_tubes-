@php
    $rewards = session('rewards');
    $hasRewards = $rewards && (count($rewards['challenges']) || count($rewards['badges']));
@endphp

@if (session('success') || session('error') || $hasRewards)
    <div class="mx-auto max-w-7xl space-y-3 px-4 pt-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($hasRewards)
            <div class="space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                @foreach ($rewards['challenges'] as $title)
                    <p>🏆 Tantangan <strong>{{ $title }}</strong> selesai! EcoPoint sudah ditambahkan.</p>
                @endforeach
                @foreach ($rewards['badges'] as $name)
                    <p>🏅 Lencana baru diraih: <strong>{{ $name }}</strong></p>
                @endforeach
            </div>
        @endif
    </div>
@endif
