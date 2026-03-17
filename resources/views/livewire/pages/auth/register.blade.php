<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        event(new Registered($user = User::create($validated)));
        Auth::login($user);
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-900 mb-1">{{ __('auth.register_title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('auth.register_subtitle') }}</p>
    </div>

    <form wire:submit="register" class="space-y-5">

        <div>
            <x-input-label for="name" :value="__('auth.name_label')" />
            <x-text-input wire:model="name" id="name" type="text" name="name"
                placeholder="{{ __('auth.name_placeholder') }}"
                required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('auth.email_label')" />
            <x-text-input wire:model="email" id="email" type="email" name="email"
                placeholder="{{ __('auth.email_placeholder') }}"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('auth.password_label')" />
            <x-text-input wire:model="password" id="password" type="password" name="password"
                placeholder="{{ __('auth.pw_placeholder') }}"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('auth.confirm_label')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password"
                name="password_confirmation"
                placeholder="{{ __('auth.password_placeholder') }}"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="mt-2">
            {{ __('auth.register_btn') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </x-primary-button>

    </form>

    <p class="text-center text-sm text-slate-500 mt-6">
        {{ __('auth.already_registered') }}
        <a href="{{ route('login') }}" wire:navigate
           class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors ml-1">
            {{ __('auth.login_link') }}
        </a>
    </p>
</div>
