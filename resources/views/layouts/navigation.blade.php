<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left Section -->
            <div class="flex items-center gap-8">

                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center">
                    <x-application-logo class="block h-8 w-auto fill-current text-blue-900" />
                </a>

                <!-- Desktop Menu -->
                <div class="hidden sm:flex items-center gap-6 text-sm font-medium">

                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 border-b-2 pb-1 transition
                        {{ request()->routeIs('dashboard')
                            ? 'border-blue-900 text-blue-900'
                            : 'border-transparent text-gray-600 hover:text-blue-900 hover:border-blue-900' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>

                    <div class="relative" x-data="{ dropdown: false }">
                        <button @click="dropdown = !dropdown"
                            class="flex items-center gap-2 border-b-2 pb-1 transition
                            {{ request()->routeIs('sub-divisions.*') || request()->routeIs('attendeds.*')
                                ? 'border-blue-900 text-blue-900'
                                : 'border-transparent text-gray-600 hover:text-blue-900 hover:border-blue-900' }}">
                            <i class="bi bi-gear"></i>
                            Data
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>

                        <div x-show="dropdown" @click.away="dropdown = false"
                            class="absolute mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-50">

                            <a href="{{ route('sub-divisions.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900">
                                <i class="bi bi-diagram-3"></i>
                                Sub Bidang
                            </a>

                            <a href="{{ route('attendeds.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900">
                                <i class="bi bi-person-badge"></i>
                                Kehadiran Pimpinan
                            </a>
                            <a href="{{ route('users.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900">
                                <i class="bi bi-people"></i>
                                Staff
                            </a>
                        </div>
                    </div>

                    <div class="relative" x-data="{ dropdownPenugasan: false }">
                        <button @click="dropdownPenugasan = !dropdownPenugasan"
                            class="flex items-center gap-2 border-b-2 pb-1 transition
        {{ request()->routeIs('assignments.*')
            ? 'border-blue-900 text-blue-900'
            : 'border-transparent text-gray-600 hover:text-blue-900 hover:border-blue-900' }}">
                            <i class="bi bi-clipboard-check"></i>
                            Penugasan
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>

                        <div x-show="dropdownPenugasan" @click.away="dropdownPenugasan = false"
                            class="absolute mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-50">

                            <a href="{{ route('assignments.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900">
                                <i class="bi bi-list-task"></i>
                                Daftar Tugas
                            </a>

                        </div>
                    </div>


                </div>
            </div>

            <!-- Right Section -->
            <div class="hidden sm:flex items-center">
                <div class="relative" x-data="{ dropdown: false }">
                    <button @click="dropdown = !dropdown"
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-900 transition">
                        {{ Auth::user()->name }}
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>

                    <div x-show="dropdown" @click.away="dropdown = false"
                        class="absolute right-0 mt-2 w-44 bg-white rounded-md shadow-lg border border-gray-100">

                        <a href="{{ route('profile.edit') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile Button -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 rounded-md text-gray-600 hover:bg-gray-100 transition">
                    <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list'"></i>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Menu -->
    <!-- Mobile Menu -->
    <div x-show="open" class="sm:hidden border-t border-gray-200 bg-white">

        <div class="px-4 py-3 space-y-2 text-sm">

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 py-2 text-gray-700 hover:text-blue-900">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <!-- Data Dropdown -->
            <div x-data="{ dataOpen: false }" class="border-t pt-3">

                <button @click="dataOpen = !dataOpen"
                    class="flex items-center justify-between w-full py-2 text-gray-700 hover:text-blue-900">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-gear"></i>
                        Data
                    </span>
                    <i class="bi" :class="dataOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>

                <div x-show="dataOpen" class="pl-6 mt-2 space-y-2">

                    <a href="{{ route('sub-divisions.index') }}"
                        class="flex items-center gap-2 py-1 text-gray-700 hover:text-blue-900">
                        <i class="bi bi-diagram-3"></i>
                        Sub Bidang
                    </a>

                    <a href="{{ route('attendeds.index') }}"
                        class="flex items-center gap-2 py-1 text-gray-700 hover:text-blue-900">
                        <i class="bi bi-person-badge"></i>
                        Kehadiran Pimpinan
                    </a>

                    <a href="{{ route('users.index') }}"
                        class="flex items-center gap-2 py-1 text-gray-700 hover:text-blue-900">
                        <i class="bi bi-people"></i>
                        Staff
                    </a>

                </div>
            </div>

            <!-- Penugasan Dropdown -->
            <div x-data="{ tugasOpen: false }" class="border-t pt-3">

                <button @click="tugasOpen = !tugasOpen"
                    class="flex items-center justify-between w-full py-2 text-gray-700 hover:text-blue-900">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-clipboard-check"></i>
                        Penugasan
                    </span>
                    <i class="bi" :class="tugasOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>

                <div x-show="tugasOpen" class="pl-6 mt-2 space-y-2">

                    <a href="{{ route('assignments.index') }}"
                        class="flex items-center gap-2 py-1 text-gray-700 hover:text-blue-900">
                        <i class="bi bi-list-task"></i>
                        Daftar Tugas
                    </a>

                </div>
            </div>

            <!-- Profile Section -->
            <div class="border-t pt-4 mt-4">
                <div class="text-gray-800 font-medium">{{ Auth::user()->name }}</div>
                <div class="text-gray-500 text-xs">{{ Auth::user()->email }}</div>

                <a href="{{ route('profile.edit') }}" class="block mt-3 text-gray-700 hover:text-blue-900">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-600">
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</nav>
