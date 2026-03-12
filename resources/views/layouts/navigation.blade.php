@php
    $isDataMaster =
        request()->routeIs('sub-divisions.*') ||
        request()->routeIs('attendeds.*') ||
        request()->routeIs('users.*');

    $isGiat = request()->routeIs('assignments.*') || request()->routeIs('assignment-users.*');
@endphp

<div x-data="{ sidebarOpen: false, dataOpen: {{ $isDataMaster ? 'true' : 'false' }}, taskOpen: {{ $isGiat ? 'true' : 'false' }} }">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur lg:hidden">
        <div class="content-shell flex h-16 items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" aria-label="Dashboard">
                <x-application-logo class="h-8 w-8 fill-current text-slate-800" />
                <span class="text-sm font-bold tracking-wide text-slate-800">ADPIM</span>
            </a>

            <button type="button" class="btn btn-secondary px-3 py-2" @click="sidebarOpen = true"
                aria-label="Buka sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </header>

    <aside class="app-sidebar fixed inset-y-0 left-0 z-30 hidden w-72 flex-col lg:flex">
        <div class="flex h-16 items-center border-b border-slate-200/80 px-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" aria-label="Dashboard">
                <x-application-logo class="h-8 w-8 fill-current text-slate-800" />
                <span class="text-base font-bold tracking-wide text-slate-800">ADPIM</span>
            </a>
        </div>

        <div class="flex flex-1 flex-col overflow-y-auto px-4 py-5">
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'app-sidebar-link-active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    Dashboard
                </a>

                <div class="pt-3">
                    <button type="button" class="app-sidebar-link w-full justify-between" @click="dataOpen = !dataOpen"
                        :aria-expanded="dataOpen.toString()">
                        <span class="inline-flex items-center gap-3">
                            <i class="bi bi-folder2-open"></i>
                            Data Master
                        </span>
                        <i class="bi bi-chevron-down text-xs transition-transform"
                            :class="dataOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-cloak x-show="dataOpen" x-transition class="mt-1 space-y-1 pl-4">
                        <a href="{{ route('sub-divisions.index') }}"
                            class="app-sidebar-link {{ request()->routeIs('sub-divisions.*') ? 'app-sidebar-link-active' : '' }}">
                            <i class="bi bi-diagram-3"></i>
                            Sub Bidang
                        </a>
                        <a href="{{ route('attendeds.index') }}"
                            class="app-sidebar-link {{ request()->routeIs('attendeds.*') ? 'app-sidebar-link-active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            Pimpinan
                        </a>
                        <a href="{{ route('users.index') }}"
                            class="app-sidebar-link {{ request()->routeIs('users.*') ? 'app-sidebar-link-active' : '' }}">
                            <i class="bi bi-people"></i>
                            Staff
                        </a>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" class="app-sidebar-link w-full justify-between" @click="taskOpen = !taskOpen"
                        :aria-expanded="taskOpen.toString()">
                        <span class="inline-flex items-center gap-3">
                            <i class="bi bi-clipboard2-check"></i>
                            Giat
                        </span>
                        <i class="bi bi-chevron-down text-xs transition-transform"
                            :class="taskOpen ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-cloak x-show="taskOpen" x-transition class="mt-1 space-y-1 pl-4">
                        <a href="{{ route('assignments.index') }}"
                            class="app-sidebar-link {{ request()->routeIs('assignments.*') ? 'app-sidebar-link-active' : '' }}">
                            <i class="bi bi-list-task"></i>
                            Data Giat
                        </a>
                        <a href="{{ route('assignment-users.index') }}"
                            class="app-sidebar-link {{ request()->routeIs('assignment-users.*') ? 'app-sidebar-link-active' : '' }}">
                            <i class="bi bi-person-check"></i>
                            Penugasan Giat
                        </a>
                    </div>
                </div>
            </nav>

            <div class="mt-auto space-y-1 border-t border-slate-200 pt-4">
                <div class="px-3 text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</div>
                <div class="px-3 text-xs text-slate-500">{{ Auth::user()->email }}</div>
                <a href="{{ route('profile.edit') }}"
                    class="app-sidebar-link {{ request()->routeIs('profile.edit') ? 'app-sidebar-link-active' : '' }}">
                    <i class="bi bi-gear"></i>
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="app-sidebar-link w-full text-rose-700 hover:bg-rose-50">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-50 lg:hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/40" @click="sidebarOpen = false"></div>

        <aside x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
            class="app-sidebar absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col">
            <div class="flex h-16 items-center justify-between border-b border-slate-200/80 px-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2" aria-label="Dashboard"
                    @click="sidebarOpen = false">
                    <x-application-logo class="h-8 w-8 fill-current text-slate-800" />
                    <span class="text-base font-bold tracking-wide text-slate-800">ADPIM</span>
                </a>

                <button type="button" class="btn btn-secondary px-3 py-2" @click="sidebarOpen = false"
                    aria-label="Tutup sidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="flex flex-1 flex-col overflow-y-auto px-4 py-5">
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}"
                        class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'app-sidebar-link-active' : '' }}"
                        @click="sidebarOpen = false">
                        <i class="bi bi-grid-1x2"></i>
                        Dashboard
                    </a>

                    <div class="pt-3">
                        <button type="button" class="app-sidebar-link w-full justify-between"
                            @click="dataOpen = !dataOpen" :aria-expanded="dataOpen.toString()">
                            <span class="inline-flex items-center gap-3">
                                <i class="bi bi-folder2-open"></i>
                                Data Master
                            </span>
                            <i class="bi bi-chevron-down text-xs transition-transform"
                                :class="dataOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-cloak x-show="dataOpen" x-transition class="mt-1 space-y-1 pl-4">
                            <a href="{{ route('sub-divisions.index') }}"
                                class="app-sidebar-link {{ request()->routeIs('sub-divisions.*') ? 'app-sidebar-link-active' : '' }}"
                                @click="sidebarOpen = false">
                                <i class="bi bi-diagram-3"></i>
                                Sub Bidang
                            </a>
                            <a href="{{ route('attendeds.index') }}"
                                class="app-sidebar-link {{ request()->routeIs('attendeds.*') ? 'app-sidebar-link-active' : '' }}"
                                @click="sidebarOpen = false">
                                <i class="bi bi-person-badge"></i>
                                Pimpinan
                            </a>
                            <a href="{{ route('users.index') }}"
                                class="app-sidebar-link {{ request()->routeIs('users.*') ? 'app-sidebar-link-active' : '' }}"
                                @click="sidebarOpen = false">
                                <i class="bi bi-people"></i>
                                Staff
                            </a>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" class="app-sidebar-link w-full justify-between"
                            @click="taskOpen = !taskOpen" :aria-expanded="taskOpen.toString()">
                            <span class="inline-flex items-center gap-3">
                                <i class="bi bi-clipboard2-check"></i>
                                Giat
                            </span>
                            <i class="bi bi-chevron-down text-xs transition-transform"
                                :class="taskOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-cloak x-show="taskOpen" x-transition class="mt-1 space-y-1 pl-4">
                            <a href="{{ route('assignments.index') }}"
                                class="app-sidebar-link {{ request()->routeIs('assignments.*') ? 'app-sidebar-link-active' : '' }}"
                                @click="sidebarOpen = false">
                                <i class="bi bi-list-task"></i>
                                Data Giat
                            </a>
                            <a href="{{ route('assignment-users.index') }}"
                                class="app-sidebar-link {{ request()->routeIs('assignment-users.*') ? 'app-sidebar-link-active' : '' }}"
                                @click="sidebarOpen = false">
                                <i class="bi bi-person-check"></i>
                                Penugasan Giat
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="mt-auto space-y-1 border-t border-slate-200 pt-4">
                    <div class="px-3 text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</div>
                    <div class="px-3 text-xs text-slate-500">{{ Auth::user()->email }}</div>
                    <a href="{{ route('profile.edit') }}"
                        class="app-sidebar-link {{ request()->routeIs('profile.edit') ? 'app-sidebar-link-active' : '' }}"
                        @click="sidebarOpen = false">
                        <i class="bi bi-gear"></i>
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="app-sidebar-link w-full text-rose-700 hover:bg-rose-50">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>
