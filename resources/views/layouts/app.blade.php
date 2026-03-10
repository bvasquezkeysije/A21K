<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @auth
        <div x-data="{ sidebarOpen: window.innerWidth >= 992 }">
            <div class="workspace-shell">
                <aside class="sidebar p-3 sidebar-column" x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>
                    @php
                        $currentUser = auth()->user();
                        $roleName = $currentUser?->getRoleNames()->first() ?? 'sin rol';
                        $isPortalUser = $currentUser?->hasRole('user') && ! $currentUser?->hasRole('admin');
                        $homeRoute = $isPortalUser ? route('portal.home') : route('dashboard');
                        $helpRoute = $isPortalUser ? route('portal.help') : route('support');
                    @endphp

                    <div class="sidebar-profile">
                        <div class="sidebar-profile-avatar" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="54" height="54" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                            </svg>
                        </div>
                        <div class="sidebar-profile-name">{{ $currentUser->name }}</div>
                        <div class="sidebar-profile-role">{{ $roleName }}</div>
                    </div>
                    <hr class="sidebar-separator">

                    <nav class="nav flex-column">
                        @if ($isPortalUser)
                            @can('portal.home.view')
                                <a href="{{ route('portal.home') }}" class="nav-link {{ request()->routeIs('portal.home') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 10.5 12 3l9 7.5"></path>
                                            <path d="M5 9.5V20h14V9.5"></path>
                                            <path d="M9.5 20v-5h5v5"></path>
                                        </svg>
                                    </span>
                                    <span>Inicio</span>
                                </a>
                            @endcan
                            @can('portal.ai.view')
                                <a href="{{ route('portal.ai') }}" class="nav-link {{ request()->routeIs('portal.ai*') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="5" y="7" width="14" height="11" rx="3"></rect>
                                            <path d="M12 3v4"></path>
                                            <path d="M9 12h.01M15 12h.01"></path>
                                            <path d="M9 15h6"></path>
                                        </svg>
                                    </span>
                                    <span>IA</span>
                                </a>
                            @endcan
                            @can('portal.forms.view')
                                <a href="{{ route('portal.forms') }}" class="nav-link {{ request()->routeIs('portal.forms') || request()->routeIs('portal.ai.exams.*') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 3h7l5 5v13H7z"></path>
                                            <path d="M14 3v5h5"></path>
                                            <path d="M10 13h6M10 17h6"></path>
                                        </svg>
                                    </span>
                                    <span>Examenes</span>
                                </a>
                            @endcan
                            @can('portal.rooms.view')
                                <a href="{{ route('portal.rooms') }}" class="nav-link {{ request()->routeIs('portal.rooms') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="6" width="18" height="14" rx="2"></rect>
                                            <path d="M7 10h3M7 14h3M14 10h3M14 14h3"></path>
                                        </svg>
                                    </span>
                                    <span>Salas</span>
                                </a>
                            @endcan
                            @can('portal.schedules.view')
                                <a href="{{ route('portal.schedules') }}" class="nav-link {{ request()->routeIs('portal.schedules') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                            <path d="M8 3v4M16 3v4M3 10h18"></path>
                                        </svg>
                                    </span>
                                    <span>Horarios</span>
                                </a>
                            @endcan
                            @can('portal.stats.view')
                                <a href="{{ route('portal.stats') }}" class="nav-link {{ request()->routeIs('portal.stats') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 20V10M10 20V6M16 20V12M22 20V4"></path>
                                        </svg>
                                    </span>
                                    <span>Estadisticas</span>
                                </a>
                            @endcan
                            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                <span class="sidebar-nav-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 20a8 8 0 0 1 16 0"></path>
                                    </svg>
                                </span>
                                <span>Perfil</span>
                            </a>
                            @can('portal.help.view')
                                <a href="{{ route('portal.help') }}" class="nav-link {{ request()->routeIs('portal.help') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M9.5 9.5a2.5 2.5 0 1 1 4.3 1.7c-.8.8-1.8 1.4-1.8 2.8"></path>
                                            <circle cx="12" cy="17" r=".7" fill="currentColor"></circle>
                                        </svg>
                                    </span>
                                    <span>Ayuda</span>
                                </a>
                            @endcan
                        @else
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                <span class="sidebar-nav-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 10.5 12 3l9 7.5"></path>
                                        <path d="M5 9.5V20h14V9.5"></path>
                                        <path d="M9.5 20v-5h5v5"></path>
                                    </svg>
                                </span>
                                <span>Dashboard</span>
                            </a>
                            @can('users.manage')
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-1.5a4.5 4.5 0 0 0-4.5-4.5H7.5A4.5 4.5 0 0 0 3 19.5V21"></path>
                                            <circle cx="9.5" cy="7" r="3.5"></circle>
                                            <path d="M20.5 21v-1a4 4 0 0 0-2.6-3.7"></path>
                                            <path d="M15.8 3.5a3.5 3.5 0 0 1 0 7"></path>
                                        </svg>
                                    </span>
                                    <span>Usuarios</span>
                                </a>
                            @endcan
                            @can('projects.view')
                                <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h4l2 2h7A2.5 2.5 0 0 1 21 8.5v9A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5v-11Z"></path>
                                            <path d="M3 10h18"></path>
                                        </svg>
                                    </span>
                                    <span>Proyectos</span>
                                </a>
                            @endcan
                            @can('tasks.view')
                                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                    <span class="sidebar-nav-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="17" rx="2.5"></rect>
                                            <path d="M8 2.5v3M16 2.5v3"></path>
                                            <path d="m8 12 2 2 4-4"></path>
                                            <path d="M7 17h10"></path>
                                        </svg>
                                    </span>
                                    <span>Tareas</span>
                                </a>
                            @endcan
                            <a href="{{ route('support') }}" class="nav-link {{ request()->routeIs('support') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                <span class="sidebar-nav-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="8"></circle>
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M7.1 7.1 9.9 9.9M14.1 14.1l2.8 2.8M16.9 7.1 14.1 9.9M9.9 14.1l-2.8 2.8"></path>
                                    </svg>
                                </span>
                                <span>Soporte</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" @click="if (window.innerWidth < 992) sidebarOpen = false">
                                <span class="sidebar-nav-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 20a8 8 0 0 1 16 0"></path>
                                    </svg>
                                </span>
                                <span>Perfil</span>
                            </a>
                        @endif
                    </nav>

                    <div class="mt-auto pt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 sidebar-logout-btn d-inline-flex align-items-center justify-content-center gap-2">
                                <span class="sidebar-logout-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <path d="M16 17l5-5-5-5"></path>
                                        <path d="M21 12H9"></path>
                                    </svg>
                                </span>
                                <span>Serrar sesion</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <button class="sidebar-backdrop d-lg-none" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" type="button" aria-label="Cerrar menu"></button>

                <div class="workspace-main">
                    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm workspace-topbar">
                        <div class="container-fluid d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <button
                                    type="button"
                                    class="btn hamburger-btn me-3"
                                    @click="sidebarOpen = !sidebarOpen"
                                    aria-label="Abrir o cerrar menu"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </button>

                                <a class="navbar-brand d-flex align-items-center mb-0" href="{{ $homeRoute }}">
                                    <img src="{{ asset('images/a21k.png') }}" alt="Logo" width="120" height="120" class="rounded">
                                </a>
                            </div>

                            <div class="dropdown ms-3">
                                <button
                                    class="btn session-card dropdown-toggle d-flex align-items-center text-start border-0 p-2"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <span class="session-avatar me-2">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                    <span class="me-2 lh-sm">
                                        <span class="d-block fw-semibold session-name">{{ auth()->user()->name }}</span>
                                        <small class="session-email">{{ auth()->user()->email }}</small>
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow session-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">Configuracion</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ $helpRoute }}">Soporte</a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">Cerrar sesion</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>

                    <main class="app-content p-4">
                        @isset($header)
                            <div class="bg-white border rounded p-3 mb-4">
                                {{ $header }}
                            </div>
                        @endisset

                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    @else
        <main class="container py-4">
            {{ $slot }}
        </main>
    @endauth

    @livewireScripts
    @stack('scripts')
</body>
</html>

