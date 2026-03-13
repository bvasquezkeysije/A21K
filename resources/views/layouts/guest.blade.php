<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'A21K') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/a21k.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/a21k.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        @if (request()->routeIs('login'))
            <div class="relative overflow-hidden bg-slate-100" style="height: var(--login-vh, 100dvh);">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_#dbeafe_0%,_#f8fafc_60%,_#f8fafc_100%)]"></div>

                <div id="login-split" class="relative mx-auto grid w-full" style="min-width: 0; height: var(--login-vh, 100dvh); grid-template-columns: 65% 35%;">
                    <div class="relative overflow-hidden" style="height: var(--login-vh, 100dvh); min-width: 0;">
                        <img
                            src="{{ asset('images/hero-login.png') }}"
                            alt="Hero login"
                            class="h-full w-full object-cover object-center"
                        >
                        <div class="absolute inset-0 bg-slate-900/10"></div>
                    </div>

                    <div class="overflow-y-auto border-l border-slate-200/70 bg-slate-50/90 backdrop-blur-[1px]" style="height: var(--login-vh, 100dvh); min-width: 0;">
                        <div class="flex h-full w-full items-center justify-center" style="padding: clamp(20px, 2.8vw, 48px); box-sizing: border-box;">
                            <div class="w-full px-1 sm:px-2" style="max-width: min(100%, 560px); margin-block: auto;">
                                <div class="mb-6 flex justify-center">
                                    <a href="/" class="inline-flex items-center justify-center">
                                        <x-application-logo class="block" style="width: min(300px, 100%); height: auto;" />
                                    </a>
                                </div>

                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_#dbeafe_0%,_#f8fafc_55%,_#f8fafc_100%)]"></div>

                <div class="relative w-full sm:max-w-md">
                    <div class="w-full rounded-2xl border border-slate-200 bg-white/95 px-6 py-5 shadow-xl shadow-slate-300/30 sm:px-8 sm:py-7">
                        <div class="mb-6 flex justify-center">
                            <a href="/" class="inline-flex items-center justify-center">
                                <x-application-logo class="block w-52 h-auto" />
                            </a>
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        @endif

        @if (request()->routeIs('login'))
            <script>
                (() => {
                    const root = document.documentElement;
                    const split = document.getElementById('login-split');

                    if (!split) {
                        return;
                    }

                    const syncLoginLayout = () => {
                        const vw = Math.max(window.innerWidth || 0, 320);
                        const vh = Math.max(window.innerHeight || 0, 520);

                        root.style.setProperty('--login-vh', `${vh}px`);

                        // Mantiene 65/35 y ajusta un minimo razonable para el panel derecho.
                        const targetRight = vw * 0.35;
                        const minRight = 420;
                        const maxRight = 700;
                        const right = Math.min(maxRight, Math.max(minRight, targetRight));
                        const left = Math.max(vw - right, 0);
                        const leftPct = ((left / vw) * 100).toFixed(3);
                        const rightPct = (100 - Number(leftPct)).toFixed(3);

                        split.style.gridTemplateColumns = `${leftPct}% ${rightPct}%`;
                    };

                    syncLoginLayout();
                    window.addEventListener('resize', syncLoginLayout, { passive: true });
                })();
            </script>
        @endif
    </body>
</html>
