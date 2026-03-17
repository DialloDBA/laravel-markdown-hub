<?php

use App\Models\PaymentGateway;
use Livewire\Volt\Component;

new class extends Component {

    public function rendering($view): void
    {
        $view->layout('layouts.admin');
    }

    public $gateways;

    public array $form = [
        'name'          => '',
        'slug'          => '',
        'icon'          => '',
        'description'   => '',
        'is_enabled'    => false,
        'is_test_mode'  => true,
        'is_default'    => false,
        'config_keys'   => [
            ['key' => '', 'value' => ''],
            ['key' => '', 'value' => ''],
            ['key' => '', 'value' => ''],
            ['key' => '', 'value' => ''],
            ['key' => '', 'value' => ''],
        ],
    ];

    public ?int $editingId      = null;
    public bool $showForm       = false;
    public ?int $deleteConfirmId = null;

    public function mount(): void
    {
        $this->loadGateways();
    }

    public function loadGateways(): void
    {
        $this->gateways = PaymentGateway::orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = [
            'name'         => '',
            'slug'         => '',
            'icon'         => '',
            'description'  => '',
            'is_enabled'   => false,
            'is_test_mode' => true,
            'is_default'   => false,
            'config_keys'  => [
                ['key' => '', 'value' => ''],
                ['key' => '', 'value' => ''],
                ['key' => '', 'value' => ''],
                ['key' => '', 'value' => ''],
                ['key' => '', 'value' => ''],
            ],
        ];
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $gateway = PaymentGateway::findOrFail($id);
        $config = $gateway->config ?? [];

        $configKeys = [];
        foreach ($config as $key => $value) {
            $configKeys[] = ['key' => $key, 'value' => $value];
        }
        while (count($configKeys) < 5) {
            $configKeys[] = ['key' => '', 'value' => ''];
        }

        $this->editingId = $id;
        $this->form = [
            'name'         => $gateway->name,
            'slug'         => $gateway->slug,
            'icon'         => $gateway->icon ?? '',
            'description'  => $gateway->description ?? '',
            'is_enabled'   => $gateway->is_enabled,
            'is_test_mode' => $gateway->is_test_mode,
            'is_default'   => $gateway->is_default,
            'config_keys'  => array_slice($configKeys, 0, 5),
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'form.name'         => 'required|string|max:255',
            'form.slug'         => 'required|string|max:255|alpha_dash',
            'form.is_enabled'   => 'boolean',
            'form.is_test_mode' => 'boolean',
            'form.is_default'   => 'boolean',
            'form.description'  => 'nullable|string|max:2000',
        ];

        if ($this->editingId) {
            $rules['form.slug'] = 'required|string|max:255|alpha_dash|unique:payment_gateways,slug,' . $this->editingId;
        } else {
            $rules['form.slug'] = 'required|string|max:255|alpha_dash|unique:payment_gateways,slug';
        }

        $this->validate($rules);

        // Build config array from key/value pairs
        $config = [];
        foreach ($this->form['config_keys'] as $pair) {
            if (!empty($pair['key'])) {
                $config[$pair['key']] = $pair['value'];
            }
        }

        $data = [
            'name'         => $this->form['name'],
            'slug'         => $this->form['slug'],
            'icon'         => $this->form['icon'] ?: null,
            'description'  => $this->form['description'] ?: null,
            'is_enabled'   => (bool) $this->form['is_enabled'],
            'is_test_mode' => (bool) $this->form['is_test_mode'],
            'is_default'   => (bool) $this->form['is_default'],
            'config'       => $config,
        ];

        // If setting as default, remove default from others
        if ($data['is_default']) {
            PaymentGateway::where('id', '!=', $this->editingId ?? 0)->update(['is_default' => false]);
        }

        if ($this->editingId) {
            PaymentGateway::findOrFail($this->editingId)->update($data);
            session()->flash('success', app()->getLocale() === 'fr' ? 'Passerelle mise à jour.' : 'Gateway updated.');
        } else {
            PaymentGateway::create($data);
            session()->flash('success', app()->getLocale() === 'fr' ? 'Passerelle créée.' : 'Gateway created.');
        }

        $this->showForm = false;
        $this->editingId = null;
        $this->loadGateways();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteConfirmId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmId = null;
    }

    public function delete(int $id): void
    {
        PaymentGateway::findOrFail($id)->delete();
        $this->deleteConfirmId = null;
        session()->flash('success', app()->getLocale() === 'fr' ? 'Passerelle supprimée.' : 'Gateway deleted.');
        $this->loadGateways();
    }

    public function toggleEnabled(int $id): void
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->update(['is_enabled' => !$gateway->is_enabled]);
        $this->loadGateways();
    }
}; ?>

<div>
    <div class="space-y-6">

        {{-- Flash message --}}
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-800">
                    {{ app()->getLocale() === 'fr' ? 'Passerelles de paiement' : 'Payment Gateways' }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ app()->getLocale() === 'fr' ? 'Configurez vos passerelles de paiement.' : 'Configure your payment gateways.' }}
                </p>
            </div>
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ app()->getLocale() === 'fr' ? 'Ajouter' : 'Add Gateway' }}
            </button>
        </div>

        {{-- Gateways Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            @if($gateways->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-slate-400 text-sm">
                        {{ app()->getLocale() === 'fr' ? 'Aucune passerelle configurée.' : 'No gateways configured yet.' }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Nom' : 'Name' }}
                                </th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Statut' : 'Status' }}
                                </th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Mode</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Défaut' : 'Default' }}
                                </th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Actif' : 'Enabled' }}
                                </th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($gateways as $gateway)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($gateway->icon)
                                            <img src="{{ $gateway->icon }}" alt="" class="w-6 h-6 rounded object-contain">
                                        @else
                                            <div class="w-6 h-6 rounded bg-slate-200 flex items-center justify-center">
                                                <span class="text-xs font-bold text-slate-500">{{ strtoupper(substr($gateway->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $gateway->name }}</p>
                                            <code class="text-xs text-slate-400">{{ $gateway->slug }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($gateway->is_enabled)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            {{ app()->getLocale() === 'fr' ? 'Actif' : 'Active' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            {{ app()->getLocale() === 'fr' ? 'Inactif' : 'Inactive' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($gateway->is_test_mode)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Test</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Live</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($gateway->is_default)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                            {{ app()->getLocale() === 'fr' ? 'Défaut' : 'Default' }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="toggleEnabled({{ $gateway->id }})"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                                                   {{ $gateway->is_enabled ? 'bg-indigo-600' : 'bg-slate-200' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                                     {{ $gateway->is_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEdit({{ $gateway->id }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $gateway->id }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Slide-in Form Panel --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex">
        <div class="flex-1 bg-black/40" wire:click="$set('showForm', false)"></div>
        <div class="w-full max-w-lg bg-white shadow-2xl flex flex-col h-full overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">
                    {{ $editingId
                        ? (app()->getLocale() === 'fr' ? 'Modifier la passerelle' : 'Edit Gateway')
                        : (app()->getLocale() === 'fr' ? 'Nouvelle passerelle' : 'New Gateway') }}
                </h3>
                <button wire:click="$set('showForm', false)" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="flex-1 px-6 py-6 space-y-5">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Nom *' : 'Name *' }}
                    </label>
                    <input wire:model="form.name" type="text" placeholder="Stripe"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('form.name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Slug *</label>
                    <input wire:model="form.slug" type="text" placeholder="stripe"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                    @error('form.slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'URL de l\'icône' : 'Icon URL' }}
                    </label>
                    <input wire:model="form.icon" type="text" placeholder="https://..."
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Description' : 'Description' }}
                    </label>
                    <textarea wire:model="form.description" rows="2"
                              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                </div>

                {{-- Config Key/Value Pairs --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'fr' ? 'Configuration (clés/valeurs chiffrées)' : 'Configuration (encrypted key/value pairs)' }}
                    </label>
                    <p class="text-xs text-slate-400 mb-3">
                        {{ app()->getLocale() === 'fr'
                            ? 'Ex: secret_key / sk_live_..., webhook_secret / whsec_...'
                            : 'Ex: secret_key / sk_live_..., webhook_secret / whsec_...' }}
                    </p>
                    <div class="space-y-2">
                        @foreach($form['config_keys'] as $index => $pair)
                        <div class="flex gap-2">
                            <input wire:model="form.config_keys.{{ $index }}.key"
                                   type="text"
                                   placeholder="{{ app()->getLocale() === 'fr' ? 'Clé' : 'Key' }}"
                                   class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                            <input wire:model="form.config_keys.{{ $index }}.value"
                                   type="password"
                                   placeholder="{{ app()->getLocale() === 'fr' ? 'Valeur' : 'Value' }}"
                                   autocomplete="new-password"
                                   class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-400">
                        {{ app()->getLocale() === 'fr' ? 'Stocké chiffré (AES-256).' : 'Stored encrypted (AES-256).' }}
                    </p>
                </div>

                {{-- Toggles --}}
                <div class="space-y-3 pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="form.is_enabled" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ app()->getLocale() === 'fr' ? 'Activer cette passerelle' : 'Enable this gateway' }}
                        </span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="form.is_test_mode" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ app()->getLocale() === 'fr' ? 'Mode test (sandbox)' : 'Test mode (sandbox)' }}
                        </span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="form.is_default" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ app()->getLocale() === 'fr' ? 'Passerelle par défaut' : 'Set as default gateway' }}
                        </span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        {{ $editingId ? (app()->getLocale() === 'fr' ? 'Mettre à jour' : 'Update') : (app()->getLocale() === 'fr' ? 'Créer' : 'Create') }}
                    </button>
                    <button type="button" wire:click="$set('showForm', false)"
                            class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                        {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($deleteConfirmId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="cancelDelete"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">
                        {{ app()->getLocale() === 'fr' ? 'Confirmer la suppression' : 'Confirm Deletion' }}
                    </h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ app()->getLocale() === 'fr' ? 'Cette action est irréversible.' : 'This action cannot be undone.' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="delete({{ $deleteConfirmId }})"
                        class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Supprimer' : 'Delete' }}
                </button>
                <button wire:click="cancelDelete"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
