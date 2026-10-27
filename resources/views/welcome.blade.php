<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'CarbonTrace') }} · Lacak jejak karbonmu</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-800 antialiased">
        <header class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-6 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <x-application-logo class="h-9 w-9" />
                <span class="text-lg font-semibold">CarbonTrace</span>
            </a>
            <nav class="flex items-center gap-2">
                @auth
                    <x-button-link :href="route('dashboard')">Dashboard</x-button-link>
                @else
                    <x-button-link :href="route('login')" variant="secondary">Masuk</x-button-link>
                    @if (Route::has('register'))
                        <x-button-link :href="route('register')">Daftar Gratis</x-button-link>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 pb-16 sm:px-6">
            <section class="py-12 text-center sm:py-20">
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">SDG 13 · Penanganan Perubahan Iklim</p>
                <h1 class="mx-auto mt-4 max-w-3xl text-4xl font-bold leading-tight text-gray-900 sm:text-5xl">
                    Ubah aktivitas harian menjadi angka yang bisa kamu kurangi.
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-600">
                    Catat perjalanan, pemakaian listrik, makanan, dan sampahmu. CarbonTrace menghitung emisinya dalam kg CO₂e,
                    lalu menantangmu menguranginya sambil mengumpulkan EcoPoint dan lencana.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @auth
                        <x-button-link :href="route('challenges.index')">Lihat tantangan</x-button-link>
                    @else
                        <x-button-link :href="route('register')">Mulai sekarang</x-button-link>
                        <x-button-link :href="route('login')" variant="secondary">Saya sudah punya akun</x-button-link>
                    @endauth
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-3xl" aria-hidden="true">📝</p>
                    <h2 class="mt-3 font-semibold text-gray-900">Catat aktivitas</h2>
                    <p class="mt-1 text-sm text-gray-600">Masukkan jarak, jam pemakaian, atau porsi. Emisinya dihitung otomatis dari faktor emisi rujukan.</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-3xl" aria-hidden="true">🏆</p>
                    <h2 class="mt-3 font-semibold text-gray-900">Ikuti tantangan</h2>
                    <p class="mt-1 text-sm text-gray-600">Tantangan berjangka mendorong satu perubahan perilaku, lengkap dengan target hemat dan hadiah EcoPoint.</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-3xl" aria-hidden="true">🏅</p>
                    <h2 class="mt-3 font-semibold text-gray-900">Kumpulkan lencana</h2>
                    <p class="mt-1 text-sm text-gray-600">Konsisten mencatat dan menyelesaikan tantangan membuka lencana pencapaian.</p>
                </div>
            </section>
        </main>
    </body>
</html>
