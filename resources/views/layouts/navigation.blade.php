<nav x-data="{ mobileOpen: false, dataOpen: false, taskOpen: false, profileOpen: false }" class="app-nav">
    <div class="content-shell">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex items-center gap-4 sm:gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2" aria-label="Dashboard">
                    <x-application-logo class="h-8 w-8 fill-current text-cyan-700" />
                    <span class="hidden text-sm font-bold tracking-wide text-slate-800 sm:block">ADPIM</span>
                </a>

                <div class="hidden items-center gap-1 sm:flex">
                    <a href="{{ route('dashboard') }}"
                        class="app-nav-link {{ request()->routeIs('dashboard') ? 'app-nav-link-active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        Dashboard
                    </a>

                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" class="app-nav-link"
                            :class="{ 'app-nav-link-active': {{ request()->routeIs('sub-divisions.*') || request()->routeIs('attendeds.*') || request()->routeIs('users.*') ? 'true' : 'false' }} }"
                            @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu">
                            <i class="bi bi-folder2-open"></i>
                            Data Master
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>
                        <div x-cloak x-show="open" x-transition @click.outside="open = false"
                            class="absolute left-0 z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                            <a href="{{ route('sub-divisions.index') }}" class="app-nav-link w-full">
                                <i class="bi bi-diagram-3"></i>
                                Sub Bidang
                            </a>
                            <a href="{{ route('attendeds.index') }}" class="app-nav-link w-full">
                                <i class="bi bi-person-badge"></i>
                                Pimpinan
                            </a>
                            <a href="{{ route('users.index') }}" class="app-nav-link w-full">
                                <i class="bi bi-people"></i>
                                Staff
                            </a>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" class="app-nav-link"
                            :class="{ 'app-nav-link-active': {{ request()->routeIs('assignments.*') || request()->routeIs('assignment-users.*') ? 'true' : 'false' }} }"
                            @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu">
                            <i class="bi bi-clipboard2-check"></i>
                            Giat
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>
                        <div x-cloak x-show="open" x-transition @click.outside="open = false"
                            class="absolute left-0 z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                            <a href="{{ route('assignments.index') }}" class="app-nav-link w-full">
                                <i class="bi bi-list-task"></i>
                                Data Giat
                            </a>
                            <a href="{{ route('assignment-users.index') }}" class="app-nav-link w-full">
                                <i class="bi bi-person-check"></i>
                                Penugasan Giat
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden items-center sm:flex">
                <div class="relative" @keydown.escape.window="profileOpen = false">
                    <button type="button" class="app-nav-link" @click="profileOpen = !profileOpen"
                        :aria-expanded="profileOpen.toString()" aria-haspopup="menu">
                        <i class="bi bi-person-circle"></i>
                        <span class="max-w-[180px] truncate">{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <div x-cloak x-show="profileOpen" x-transition @click.outside="profileOpen = false"
                        class="absolute right-0 z-50 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="app-nav-link w-full">
                            <i class="bi bi-gear"></i>
                            Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="app-nav-link w-full text-rose-700 hover:bg-rose-50">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary px-3 py-2 sm:hidden" @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen.toString()" aria-label="Buka menu navigasi">
                <i class="bi" :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
        </div>
    </div>

    <div x-cloak x-show="mobileOpen" x-transition class="border-t border-slate-200 bg-white sm:hidden">
        <div class="content-shell py-3">
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="app-nav-link w-full">
                    <i class="bi bi-grid-1x2"></i>
                    Dashboard
                </a>

                <button type="button" class="app-nav-link w-full justify-between" @click="dataOpen = !dataOpen"
                    :aria-expanded="dataOpen.toString()">
                    <span class="inline-flex items-center gap-2">
                        <i class="bi bi-folder2-open"></i> Data Master
                    </span>
                    <i class="bi" :class="dataOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div x-cloak x-show="dataOpen" class="space-y-1 pl-4">
                    <a href="{{ route('sub-divisions.index') }}" class="app-nav-link w-full">Sub Bidang</a>
                    <a href="{{ route('attendeds.index') }}" class="app-nav-link w-full">Pimpinan</a>
                    <a href="{{ route('users.index') }}" class="app-nav-link w-full">Staff</a>
                </div>

                <button type="button" class="app-nav-link w-full justify-between" @click="taskOpen = !taskOpen"
                    :aria-expanded="taskOpen.toString()">
                    <span class="inline-flex items-center gap-2">
                        <i class="bi bi-clipboard2-check"></i> Giat
                    </span>
                    <i class="bi" :class="taskOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div x-cloak x-show="taskOpen" class="space-y-1 pl-4">
                    <a href="{{ route('assignments.index') }}" class="app-nav-link w-full">Data Giat</a>
                    <a href="{{ route('assignment-users.index') }}" class="app-nav-link w-full">Penugasan Giat</a>
                </div>
            </div>

            <div class="mt-4 space-y-1 border-t border-slate-200 pt-3">
                <div class="px-3 text-sm font-semibold text-slate-700">{{ Auth::user()->name }}</div>
                <div class="px-3 text-xs text-slate-500">{{ Auth::user()->email }}</div>
                <a href="{{ route('profile.edit') }}" class="app-nav-link w-full">
                    <i class="bi bi-gear"></i>
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="app-nav-link w-full text-rose-700 hover:bg-rose-50">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
