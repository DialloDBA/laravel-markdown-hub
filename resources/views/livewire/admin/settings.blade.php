<?php

use App\Models\AdminSetting;
use App\Models\AiProvider;
use Livewire\Volt\Component;

new class extends Component {

    public function rendering($view): void
    {
        $view->layout('layouts.admin');
    }

    public string $activeTab = 'general';

    // General settings
    public string $app_name          = '';
    public string $app_url           = '';
    public bool   $maintenance_mode  = false;

    // AI settings
    public string $default_ai_provider         = '';
    public string $ai_requests_per_day_free    = '10';
    public string $ai_requests_per_day_pro     = '200';

    // Subscription settings
    public bool   $enable_subscriptions = true;
    public string $trial_days           = '14';
    public string $default_currency     = 'USD';

    // Email settings
    public string $mail_from_name    = '';
    public string $mail_from_address = '';
    public bool   $mail_notifications = true;

    public $aiProviders;

    public function mount(): void
    {
        $this->aiProviders = AiProvider::orderBy('name')->get();

        // General
        $this->app_name         = AdminSetting::get('app_name', config('app.name', 'MarkdownHub'));
        $this->app_url          = AdminSetting::get('app_url', config('app.url', ''));
        $this->maintenance_mode = (bool) AdminSetting::get('maintenance_mode', false);

        // AI
        $this->default_ai_provider      = AdminSetting::get('default_ai_provider', '');
        $this->ai_requests_per_day_free = AdminSetting::get('ai_requests_per_day_free', '10');
        $this->ai_requests_per_day_pro  = AdminSetting::get('ai_requests_per_day_pro', '200');

        // Subscription
        $this->enable_subscriptions = (bool) AdminSetting::get('enable_subscriptions', true);
        $this->trial_days           = AdminSetting::get('trial_days', '14');
        $this->default_currency     = AdminSetting::get('default_currency', 'USD');

        // Email
        $this->mail_from_name     = AdminSetting::get('mail_from_name', config('mail.from.name', ''));
        $this->mail_from_address  = AdminSetting::get('mail_from_address', config('mail.from.address', ''));
        $this->mail_notifications = (bool) AdminSetting::get('mail_notifications', true);
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'app_name'        => 'required|string|max:255',
            'app_url'         => 'nullable|url|max:500',
            'maintenance_mode'=> 'boolean',
        ]);

        AdminSetting::set('app_name',         $this->app_name,         'general');
        AdminSetting::set('app_url',          $this->app_url,          'general');
        AdminSetting::set('maintenance_mode', $this->maintenance_mode ? '1' : '0', 'general');

        session()->flash('success_general', app()->getLocale() === 'fr' ? 'Paramètres généraux sauvegardés.' : 'General settings saved.');
    }

    public function saveEmail(): void
    {
        $this->validate([
            'mail_from_name'     => 'nullable|string|max:255',
            'mail_from_address'  => 'nullable|email|max:255',
            'mail_notifications' => 'boolean',
        ]);

        AdminSetting::set('mail_from_name',    $this->mail_from_name,    'email');
        AdminSetting::set('mail_from_address', $this->mail_from_address, 'email');
        AdminSetting::set('mail_notifications', $this->mail_notifications ? '1' : '0', 'email');

        session()->flash('success_email', app()->getLocale() === 'fr' ? 'Paramètres email sauvegardés.' : 'Email settings saved.');
    }

    public function saveAi(): void
    {
        $this->validate([
            'default_ai_provider'        => 'nullable|string|max:255',
            'ai_requests_per_day_free'   => 'required|integer|min:0',
            'ai_requests_per_day_pro'    => 'required|integer|min:0',
        ]);

        AdminSetting::set('default_ai_provider',       $this->default_ai_provider,       'ai');
        AdminSetting::set('ai_requests_per_day_free',  $this->ai_requests_per_day_free,  'ai');
        AdminSetting::set('ai_requests_per_day_pro',   $this->ai_requests_per_day_pro,   'ai');

        session()->flash('success_ai', app()->getLocale() === 'fr' ? 'Paramètres IA sauvegardés.' : 'AI settings saved.');
    }

    public function saveSubscription(): void
    {
        $this->validate([
            'enable_subscriptions' => 'boolean',
            'trial_days'           => 'required|integer|min:0|max:365',
            'default_currency'     => 'required|string|max:10',
        ]);

        AdminSetting::set('enable_subscriptions', $this->enable_subscriptions ? '1' : '0', 'subscription');
        AdminSetting::set('trial_days',           $this->trial_days,                        'subscription');
        AdminSetting::set('default_currency',     strtoupper($this->default_currency),       'subscription');

        session()->flash('success_subscription', app()->getLocale() === 'fr' ? 'Paramètres abonnement sauvegardés.' : 'Subscription settings saved.');
    }
}; ?>

<div>
    <div class="max-w-3xl space-y-6">

        <div>
            <h2 class="text-xl font-black text-slate-800">
                {{ app()->getLocale() === 'fr' ? 'Paramètres de l\'application' : 'Application Settings' }}
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ app()->getLocale() === 'fr' ? 'Configurez les paramètres globaux de l\'application.' : 'Configure global application settings.' }}
            </p>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">
            @foreach([
                ['key' => 'general',      'fr' => 'Général',       'en' => 'General'],
                ['key' => 'email',        'fr' => 'Email',         'en' => 'Email'],
                ['key' => 'ai',           'fr' => 'IA',            'en' => 'AI'],
                ['key' => 'subscription', 'fr' => 'Abonnement',    'en' => 'Subscription'],
            ] as $tab)
            <button wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors
                           {{ $activeTab === $tab['key'] ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ app()->getLocale() === 'fr' ? $tab['fr'] : $tab['en'] }}
            </button>
            @endforeach
        </div>

        {{-- General Tab --}}
        @if($activeTab === 'general')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            @if(session('success_general'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                {{ session('success_general') }}
            </div>
            @endif

            <form wire:submit="saveGeneral" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Nom de l\'application *' : 'Application Name *' }}
                    </label>
                    <input wire:model="app_name" type="text"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('app_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'URL de l\'application' : 'Application URL' }}
                    </label>
                    <input wire:model="app_url" type="url" placeholder="https://example.com"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('app_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="maintenance_mode" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-bold text-amber-800">
                                {{ app()->getLocale() === 'fr' ? 'Mode maintenance' : 'Maintenance Mode' }}
                            </span>
                            <p class="text-xs text-amber-600 mt-0.5">
                                {{ app()->getLocale() === 'fr'
                                    ? 'Active la page de maintenance pour les utilisateurs non-admin.'
                                    : 'Shows a maintenance page to non-admin users.' }}
                            </p>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Email Tab --}}
        @if($activeTab === 'email')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            @if(session('success_email'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                {{ session('success_email') }}
            </div>
            @endif

            <form wire:submit="saveEmail" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Nom de l\'expéditeur' : 'From Name' }}
                    </label>
                    <input wire:model="mail_from_name" type="text" placeholder="MarkdownHub"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('mail_from_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Adresse email de l\'expéditeur' : 'From Email Address' }}
                    </label>
                    <input wire:model="mail_from_address" type="email" placeholder="noreply@example.com"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('mail_from_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="mail_notifications" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ app()->getLocale() === 'fr' ? 'Activer les notifications email' : 'Enable email notifications' }}
                        </span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- AI Tab --}}
        @if($activeTab === 'ai')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            @if(session('success_ai'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                {{ session('success_ai') }}
            </div>
            @endif

            <form wire:submit="saveAi" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Fournisseur IA par défaut' : 'Default AI Provider' }}
                    </label>
                    <select wire:model="default_ai_provider"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="">{{ app()->getLocale() === 'fr' ? '— Aucun —' : '— None —' }}</option>
                        @foreach($aiProviders as $provider)
                        <option value="{{ $provider->slug }}">{{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            {{ app()->getLocale() === 'fr' ? 'Requêtes IA/jour (plan gratuit)' : 'AI Requests/day (free plan)' }}
                        </label>
                        <input wire:model="ai_requests_per_day_free" type="number" min="0"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('ai_requests_per_day_free') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            {{ app()->getLocale() === 'fr' ? 'Requêtes IA/jour (plan pro)' : 'AI Requests/day (pro plan)' }}
                        </label>
                        <input wire:model="ai_requests_per_day_pro" type="number" min="0"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('ai_requests_per_day_pro') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Subscription Tab --}}
        @if($activeTab === 'subscription')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            @if(session('success_subscription'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                {{ session('success_subscription') }}
            </div>
            @endif

            <form wire:submit="saveSubscription" class="space-y-5">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="enable_subscriptions" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-sm font-bold text-slate-700">
                                {{ app()->getLocale() === 'fr' ? 'Activer les abonnements' : 'Enable Subscriptions' }}
                            </span>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ app()->getLocale() === 'fr'
                                    ? 'Active/désactive le système d\'abonnement dans l\'application.'
                                    : 'Enable or disable the subscription system in the app.' }}
                            </p>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            {{ app()->getLocale() === 'fr' ? 'Durée d\'essai (jours)' : 'Trial Period (days)' }}
                        </label>
                        <input wire:model="trial_days" type="number" min="0" max="365"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('trial_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            {{ app()->getLocale() === 'fr' ? 'Devise par défaut' : 'Default Currency' }}
                        </label>
                        <input wire:model="default_currency" type="text" placeholder="USD" maxlength="10"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono uppercase">
                        @error('default_currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
        @endif

    </div>
</div>
