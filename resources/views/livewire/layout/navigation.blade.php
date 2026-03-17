<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

{{--
    This navigation component is kept for Livewire compatibility.
    The main navigation is handled by the sidebar in layouts/app.blade.php.
    This component provides the logout action via Livewire wire:click="logout".
--}}
<div>
    {{-- Minimal top-right user dropdown for pages that still use livewire:layout.navigation --}}
    @auth
    <div x-data="{ open: false }" class="relative inline-block text-left">
        <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-sm">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-xs font-black shrink-0"
                 x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                 x-on:profile-updated.window="name = $event.detail.name">
                <span x-text="name.charAt(0).toUpperCase()"></span>
            </div>
            <span class="hidden sm:block font-semibold text-slate-700 max-w-[100px] truncate"
                  x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                  x-text="name"
                  x-on:profile-updated.window="name = $event.detail.name"></span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" @click.away="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
            <a href="{{ route('profile') }}" wire:navigate @click="open = false"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ app()->getLocale() === 'fr' ? 'Profil' : 'Profile' }}
            </a>
            <div class="border-t border-slate-100">
                <button wire:click="logout" @click="open = false"
                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    {{ app()->getLocale() === 'fr' ? 'Se déconnecter' : 'Log Out' }}
                </button>
            </div>
        </div>
    </div>
    @endauth
</div>
