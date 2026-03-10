<x-guest-layout>
    <div class="mb-7 text-center">
        <h1 class="text-2xl font-semibold text-slate-900">Bienvenido</h1>
        <p class="mt-2 text-sm text-slate-500">Inicia sesion con tu usuario o correo.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="login" :value="__('Correo o usuario')" class="text-xs font-medium uppercase tracking-wide text-slate-500" />
            <x-text-input
                id="login"
                class="mt-2 block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                type="text"
                name="login"
                :value="old('login', old('email'))"
                required
                autofocus
                autocomplete="username"
                placeholder="usuario o correo@dominio.com"
            />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contrasena')" class="text-xs font-medium uppercase tracking-wide text-slate-500" />

            <div class="relative mt-2">
                <x-text-input
                    id="password"
                    class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 pr-11 text-slate-900 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contrasena"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 transition hover:text-slate-700 focus:outline-none"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena'"
                >
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.585 10.586a2 2 0 0 0 2.828 2.828M9.88 5.09A10.94 10.94 0 0 1 12 5c4.477 0 8.268 2.943 9.542 7a10.82 10.82 0 0 1-3.207 4.55M6.228 6.228A10.82 10.82 0 0 0 2.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.14-.38 4.496-1.058" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center text-sm text-slate-600">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-800 shadow-sm focus:ring-slate-500" name="remember">
                <span class="ms-2">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-slate-600 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Olvidaste tu contrasena?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold normal-case tracking-normal hover:bg-slate-800 focus:bg-slate-800 focus:ring-slate-500">
            {{ __('Iniciar sesion') }}
        </x-primary-button>
    </form>
</x-guest-layout>
