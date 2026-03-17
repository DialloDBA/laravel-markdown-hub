<?php

use App\Models\AiProvider;
use Livewire\Volt\Component;

new class extends Component {

    public function rendering($view): void
    {
        $view->layout('layouts.admin');
    }

    public $providers;

    public array $form = [
        'name'        => '',
        'slug'        => '',
        'icon'        => '',
        'base_url'    => '',
        'api_key'     => '',
        'models_raw'  => '',
        'is_enabled'  => true,
        'description' => '',
        'sort_order'  => 0,
    ];

    public ?int $editingId      = null;
    public bool $showForm       = false;
    public ?int $deleteConfirmId = null;

    public function mount(): void
    {
        $this->loadProviders();
    }

    public function loadProviders(): void
    {
        $this->providers = AiProvider::orderBy('sort_order')->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = [
            'name'        => '',
            'slug'        => '',
            'icon'        => '',
            'base_url'    => '',
            'api_key'     => '',
            'models_raw'  => '',
            'is_enabled'  => true,
            'description' => '',
            'sort_order'  => 0,
        ];
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $provider = AiProvider::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'name'        => $provider->name,
            'slug'        => $provider->slug,
            'icon'        => $provider->icon ?? '',
            'base_url'    => $provider->base_url ?? '',
            'api_key'     => '', // don't pre-fill for security
            'models_raw'  => implode(', ', $provider->models ?? []),
            'is_enabled'  => $provider->is_enabled,
            'description' => $provider->description ?? '',
            'sort_order'  => $provider->sort_order,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'form.name'       => 'required|string|max:255',
            'form.slug'       => 'required|string|max:255|alpha_dash',
            'form.base_url'   => 'nullable|url|max:500',
            'form.api_key'    => 'nullable|string|max:1000',
            'form.models_raw' => 'nullable|string',
            'form.is_enabled' => 'boolean',
            'form.sort_order' => 'integer|min:0',
            'form.description'=> 'nullable|string|max:2000',
        ];

        if ($this->editingId) {
            $rules['form.slug'] = 'required|string|max:255|alpha_dash|unique:ai_providers,slug,' . $this->editingId;
        } else {
            $rules['form.slug'] = 'required|string|max:255|alpha_dash|unique:ai_providers,slug';
        }

        $this->validate($rules);

        $models = array_values(array_filter(array_map('trim', explode(',', $this->form['models_raw'] ?? ''))));

        $data = [
            'name'        => $this->form['name'],
            'slug'        => $this->form['slug'],
            'icon'        => $this->form['icon'] ?: null,
            'base_url'    => $this->form['base_url'] ?: null,
            'models'      => $models,
            'is_enabled'  => (bool) $this->form['is_enabled'],
            'description' => $this->form['description'] ?: null,
            'sort_order'  => (int) $this->form['sort_order'],
        ];

        if (!empty($this->form['api_key'])) {
            $data['api_key'] = $this->form['api_key'];
        }

        if ($this->editingId) {
            AiProvider::findOrFail($this->editingId)->update($data);
            session()->flash('success', app()->getLocale() === 'fr' ? 'Fournisseur mis à jour.' : 'Provider updated.');
        } else {
            AiProvider::create($data);
            session()->flash('success', app()->getLocale() === 'fr' ? 'Fournisseur créé.' : 'Provider created.');
        }

        $this->showForm = false;
        $this->editingId = null;
        $this->loadProviders();
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
        AiProvider::findOrFail($id)->delete();
        $this->deleteConfirmId = null;
        session()->flash('success', app()->getLocale() === 'fr' ? 'Fournisseur supprimé.' : 'Provider deleted.');
        $this->loadProviders();
    }

    public function toggleEnabled(int $id): void
    {
        $provider = AiProvider::findOrFail($id);
        $provider->update(['is_enabled' => !$provider->is_enabled]);
        $this->loadProviders();
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
                    {{ app()->getLocale() === 'fr' ? 'Fournisseurs IA / AI Providers' : 'AI Providers' }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ app()->getLocale() === 'fr' ? 'Gérez les fournisseurs d\'IA disponibles.' : 'Manage available AI providers.' }}
                </p>
            </div>
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ app()->getLocale() === 'fr' ? 'Ajouter' : 'Add Provider' }}
            </button>
        </div>

        {{-- Providers Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            @if($providers->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                    </svg>
                    <p class="text-slate-400 text-sm">
                        {{ app()->getLocale() === 'fr' ? 'Aucun fournisseur configuré.' : 'No providers configured yet.' }}
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
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Slug</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Modèles' : 'Models' }}
                                </th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Base URL</th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Actif' : 'Enabled' }}
                                </th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    {{ app()->getLocale() === 'fr' ? 'Actions' : 'Actions' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($providers as $provider)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($provider->icon)
                                            <img src="{{ $provider->icon }}" alt="" class="w-6 h-6 rounded object-contain">
                                        @else
                                            <div class="w-6 h-6 rounded bg-slate-200 flex items-center justify-center">
                                                <span class="text-xs font-bold text-slate-500">{{ strtoupper(substr($provider->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span class="font-semibold text-slate-800">{{ $provider->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">{{ $provider->slug }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        {{ count($provider->models ?? []) }} {{ app()->getLocale() === 'fr' ? 'modèles' : 'models' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs max-w-xs truncate">
                                    {{ $provider->base_url ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="toggleEnabled({{ $provider->id }})"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none
                                                   {{ $provider->is_enabled ? 'bg-indigo-600' : 'bg-slate-200' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                                     {{ $provider->is_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEdit({{ $provider->id }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $provider->id }})"
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
                        ? (app()->getLocale() === 'fr' ? 'Modifier le fournisseur' : 'Edit Provider')
                        : (app()->getLocale() === 'fr' ? 'Nouveau fournisseur' : 'New Provider') }}
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
                    <input wire:model="form.name" type="text" placeholder="OpenAI"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('form.name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Slug *</label>
                    <input wire:model="form.slug" type="text" placeholder="openai"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                    @error('form.slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'URL de base' : 'Base URL' }}
                    </label>
                    <input wire:model="form.base_url" type="url" placeholder="https://api.openai.com/v1"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('form.base_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        API Key {{ $editingId ? '(' . (app()->getLocale() === 'fr' ? 'laisser vide pour ne pas changer' : 'leave blank to keep current') . ')' : '' }}
                    </label>
                    <input wire:model="form.api_key" type="password" placeholder="sk-..."
                           autocomplete="new-password"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                    <p class="mt-1 text-xs text-slate-400">
                        {{ app()->getLocale() === 'fr' ? 'Stockée chiffrée (AES-256).' : 'Stored encrypted (AES-256).' }}
                    </p>
                    @error('form.api_key') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        {{ app()->getLocale() === 'fr' ? 'Modèles disponibles (séparés par des virgules)' : 'Available Models (comma-separated)' }}
                    </label>
                    <input wire:model="form.models_raw" type="text"
                           placeholder="gpt-4o, gpt-4o-mini, gpt-3.5-turbo"
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('form.models_raw') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            {{ app()->getLocale() === 'fr' ? 'Ordre d\'affichage' : 'Sort Order' }}
                        </label>
                        <input wire:model="form.sort_order" type="number" min="0"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="flex items-end pb-2.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="form.is_enabled" type="checkbox"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ app()->getLocale() === 'fr' ? 'Activer' : 'Enable' }}
                            </span>
                        </label>
                    </div>
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
