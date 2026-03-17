<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-900 mb-1">{{ __('auth.login_title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('auth.login_subtitle') }}</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">

        <div>
            <x-input-label for="email" :value="__('auth.email_label')" />
            <x-text-input wire:model="form.email" id="email" type="email" name="email"
                placeholder="{{ __('auth.email_placeholder') }}"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('auth.password_label')" class="mb-0" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>
            <x-text-input wire:model="form.password" id="password" type="password" name="password"
                placeholder="{{ __('auth.password_placeholder') }}"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" />
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input wire:model="form.remember" id="remember" type="checkbox"
                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 transition">
            <span class="text-sm text-slate-600">{{ __('auth.remember_me') }}</span>
        </label>

        <x-primary-button class="mt-2">
            {{ __('auth.login_btn') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </x-primary-button>

    </form>

    @if (Route::has('register'))
        <p class="text-center text-sm text-slate-500 mt-6">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" wire:navigate
               class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors ml-1">
                {{ __('auth.register_link') }}
            </a>
        </p>
    @endif
</div>
