<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="page-section">
        <div class="content-shell space-y-6">
            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
