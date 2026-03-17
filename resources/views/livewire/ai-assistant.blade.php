<?php

use App\Models\AiProvider;
use App\Models\UserAiSetting;
use App\Models\ReadmeFile;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;

new class extends Component {

    public function rendering($view): void
    {
        $view->layout('layouts.app');
    }

    public $providers;
    public ?int $selectedProviderId = null;
    public ?string $selectedModel   = null;
    public string  $userPrompt      = '';
    public array   $conversation    = [];
    public bool    $isLoading       = false;
    public $userAiSetting           = null;
    public array   $availableModels = [];
    public string  $saveTitle       = '';
    public bool    $showSaveModal   = false;
    public ?string $lastAiContent   = null;
    public string  $customApiKey   = '';

    public function mount(): void
    {
        $this->providers = AiProvider::enabled()->get();

        $this->userAiSetting = UserAiSetting::where('user_id', auth()->id())->with('provider')->first();

        if ($this->userAiSetting && $this->userAiSetting->ai_provider_id) {
            $this->selectedProviderId = $this->userAiSetting->ai_provider_id;
            $this->selectedModel      = $this->userAiSetting->selected_model;
            $this->customApiKey       = $this->userAiSetting->custom_api_key ?? '';
            $this->updateAvailableModels();
        } elseif ($this->providers->isNotEmpty()) {
            $this->selectedProviderId = $this->providers->first()->id;
            $this->updateAvailableModels();
            if (!empty($this->availableModels)) {
                $this->selectedModel = $this->availableModels[0];
            }
        }
    }

    public function selectProvider(int $id): void
    {
        $this->selectedProviderId = $id;
        $this->selectedModel = null;
        $this->updateAvailableModels();
        if (!empty($this->availableModels)) {
            $this->selectedModel = $this->availableModels[0];
        }
    }

    public function selectModel(string $model): void
    {
        $this->selectedModel = $model;
    }

    protected function updateAvailableModels(): void
    {
        if (!$this->selectedProviderId) {
            $this->availableModels = [];
            return;
        }
        $provider = AiProvider::find($this->selectedProviderId);
        $this->availableModels = $provider ? ($provider->models ?? []) : [];
    }

    public function savePreferences(): void
    {
        UserAiSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'ai_provider_id' => $this->selectedProviderId,
                'selected_model' => $this->selectedModel,
                'custom_api_key' => $this->customApiKey ?: null,
            ]
        );
        session()->flash('prefs_saved', app()->getLocale() === 'fr'
            ? 'Préférences sauvegardées.'
            : 'Preferences saved.');
    }

    public function sendMessage(): void
    {
        $prompt = trim($this->userPrompt);
        if (empty($prompt)) {
            return;
        }

        if (!$this->selectedProviderId || !$this->selectedModel) {
            session()->flash('error', app()->getLocale() === 'fr'
                ? 'Veuillez sélectionner un fournisseur et un modèle.'
                : 'Please select a provider and model first.');
            return;
        }

        $this->conversation[] = ['role' => 'user', 'content' => $prompt];
        $this->userPrompt = '';
        $this->isLoading = true;

        $provider = AiProvider::find($this->selectedProviderId);
        if (!$provider) {
            $this->conversation[] = ['role' => 'assistant', 'content' => 'Provider not found.'];
            $this->isLoading = false;
            return;
        }

        // Determine API key: inline field > saved user key > provider global key
        $apiKey = $this->customApiKey
            ?: ($this->userAiSetting?->custom_api_key ?? null)
            ?: $provider->api_key;

        if (empty($apiKey)) {
            $this->conversation[] = ['role' => 'assistant', 'content' => app()->getLocale() === 'fr'
                ? "⚠️ Aucune clé API configurée pour **{$provider->name}**.\n\nL'administrateur doit ajouter une clé API dans le panneau Admin → Fournisseurs IA, ou vous pouvez renseigner votre propre clé ci-contre dans « Ma clé API »."
                : "⚠️ No API key configured for **{$provider->name}**.\n\nThe administrator must add an API key in Admin → AI Providers, or you can enter your own key in the \"My API Key\" field on the left."];
            $this->isLoading = false;
            return;
        }

        $baseUrl = rtrim($provider->base_url ?? 'https://api.openai.com/v1', '/');
        $endpoint = $baseUrl . '/chat/completions';

        // Build messages for the API (OpenAI-compatible format)
        $messages = [];
        $messages[] = [
            'role'    => 'system',
            'content' => 'You are a helpful assistant specialized in writing and improving README documentation files using Markdown. Provide clear, well-structured, and professional responses.',
        ];
        foreach ($this->conversation as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post($endpoint, [
                    'model'    => $this->selectedModel,
                    'messages' => $messages,
                    'stream'   => false,
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? 'No response.';
                $this->conversation[] = ['role' => 'assistant', 'content' => $content];
                $this->lastAiContent  = $content;
            } else {
                $error = $response->json('error.message') ?? $response->body();
                $this->conversation[] = ['role' => 'assistant', 'content' => 'API Error: ' . $error];
            }
        } catch (\Throwable $e) {
            $this->conversation[] = ['role' => 'assistant', 'content' => 'Connection error: ' . $e->getMessage()];
        }

        $this->isLoading = false;
    }

    public function clearConversation(): void
    {
        $this->conversation  = [];
        $this->lastAiContent = null;
    }

    public function openSaveModal(): void
    {
        $this->saveTitle   = 'AI Response ' . now()->format('Y-m-d H:i');
        $this->showSaveModal = true;
    }

    public function saveAsDocument(): void
    {
        if (!$this->lastAiContent) {
            return;
        }

        $title = trim($this->saveTitle) ?: 'AI Response ' . now()->format('Y-m-d H:i');

        ReadmeFile::create([
            'user_id' => auth()->id(),
            'name'    => $title,
            'content' => $this->lastAiContent,
        ]);

        $this->showSaveModal = false;
        session()->flash('doc_saved', app()->getLocale() === 'fr'
            ? 'Document sauvegardé avec succès.'
            : 'Document saved successfully.');
    }
}; ?>

<div>
    <div class="h-full flex flex-col lg:flex-row overflow-hidden" style="height: calc(100vh - 57px);">

        {{-- ── Left panel: provider & model selection ── --}}
        <div class="w-full lg:w-72 xl:w-80 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 flex flex-col overflow-y-auto shrink-0">

            <div class="px-4 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Fournisseur' : 'AI Provider' }}
                </h2>
            </div>

            {{-- Flash messages --}}
            <div class="px-4 pt-3">
                @if(session('prefs_saved'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 rounded-xl text-xs font-medium mb-3">
                    {{ session('prefs_saved') }}
                </div>
                @endif
                @if(session('doc_saved'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-2 rounded-xl text-xs font-medium mb-3">
                    {{ session('doc_saved') }}
                </div>
                @endif
                @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-xl text-xs font-medium mb-3">
                    {{ session('error') }}
                </div>
                @endif
            </div>

            {{-- Providers list --}}
            <div class="px-4 py-3 space-y-2">
                @forelse($providers as $provider)
                <button wire:click="selectProvider({{ $provider->id }})"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-xl border text-left transition-all
                               {{ $selectedProviderId === $provider->id
                                   ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-200'
                                   : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50' }}">
                    @if($provider->icon)
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-base leading-none">
                            {{ $provider->icon }}
                        </div>
                    @else
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center text-white text-xs font-black shrink-0">
                            {{ strtoupper(substr($provider->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $provider->name }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ count($provider->models ?? []) }} {{ app()->getLocale() === 'fr' ? 'modèles' : 'models' }}
                        </p>
                    </div>
                    @if($selectedProviderId === $provider->id)
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    @endif
                </button>
                @empty
                <div class="text-center py-6">
                    <p class="text-xs text-slate-400">
                        {{ app()->getLocale() === 'fr' ? 'Aucun fournisseur actif.' : 'No active providers.' }}
                    </p>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.ai-providers') }}"
                       class="inline-block mt-2 text-xs text-indigo-600 hover:text-indigo-700 font-semibold">
                        {{ app()->getLocale() === 'fr' ? 'Configurer →' : 'Configure →' }}
                    </a>
                    @endif
                </div>
                @endforelse
            </div>

            {{-- Model selector --}}
            @if($selectedProviderId && !empty($availableModels))
            <div class="px-4 py-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Modèle' : 'Model' }}
                </label>
                <select wire:change="selectModel($event.target.value)"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                    @foreach($availableModels as $model)
                    <option value="{{ $model }}" {{ $selectedModel === $model ? 'selected' : '' }}>{{ $model }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Custom API key --}}
            <div class="px-4 py-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Ma clé API (optionnel)' : 'My API Key (optional)' }}
                </label>
                <input wire:model="customApiKey"
                       type="password"
                       placeholder="sk-..."
                       class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white font-mono"
                       autocomplete="off">
                <p class="text-xs text-slate-400 mt-1">
                    {{ app()->getLocale() === 'fr'
                        ? 'Remplace la clé globale du fournisseur.'
                        : 'Overrides the provider\'s global key.' }}
                </p>
            </div>

            {{-- Save preferences --}}
            <div class="px-4 py-3 border-t border-slate-100 mt-auto">
                <button wire:click="savePreferences"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Sauvegarder mes préférences' : 'Save Preferences' }}
                </button>
            </div>
        </div>

        {{-- ── Right panel: chat ── --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-slate-50">

            {{-- Chat header --}}
            <div class="bg-white border-b border-slate-200 px-5 py-3.5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">
                            {{ app()->getLocale() === 'fr' ? 'Assistant IA' : 'AI Assistant' }}
                        </p>
                        @if($selectedModel)
                        <p class="text-xs text-slate-400">{{ $selectedModel }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($lastAiContent)
                    <button wire:click="openSaveModal"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors border border-emerald-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save as doc' }}
                    </button>
                    @endif
                    @if(!empty($conversation))
                    <button wire:click="clearConversation"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            title="{{ app()->getLocale() === 'fr' ? 'Effacer la conversation' : 'Clear conversation' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Conversation --}}
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-4" id="chat-messages">
                @if(empty($conversation))
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    @if($providers->isEmpty())
                    {{-- No providers configured --}}
                    <div class="w-16 h-16 rounded-2xl bg-amber-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-700 text-lg mb-2">
                        {{ app()->getLocale() === 'fr' ? 'Aucun fournisseur IA actif' : 'No AI Provider Enabled' }}
                    </h3>
                    <p class="text-sm text-slate-400 max-w-xs">
                        {{ app()->getLocale() === 'fr'
                            ? 'Un administrateur doit d\'abord activer un fournisseur IA et configurer sa clé API.'
                            : 'An administrator must first enable an AI provider and configure its API key.' }}
                    </p>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.ai-providers') }}"
                       class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ app()->getLocale() === 'fr' ? 'Configurer un fournisseur' : 'Configure a Provider' }}
                    </a>
                    @endif
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-700 text-lg mb-2">
                        {{ app()->getLocale() === 'fr' ? 'Commencez la conversation' : 'Start a conversation' }}
                    </h3>
                    <p class="text-sm text-slate-400 max-w-xs">
                        {{ app()->getLocale() === 'fr'
                            ? 'Demandez à l\'IA de vous aider à rédiger ou améliorer votre documentation README.'
                            : 'Ask the AI to help you write or improve your README documentation.' }}
                    </p>

                    {{-- Example prompts --}}
                    <div class="mt-6 grid grid-cols-1 gap-2 w-full max-w-md">
                        @foreach([
                            app()->getLocale() === 'fr' ? 'Génère un README pour une API REST en Laravel' : 'Generate a README for a Laravel REST API',
                            app()->getLocale() === 'fr' ? 'Améliore ce README et ajoute des badges' : 'Improve my README and add badges',
                            app()->getLocale() === 'fr' ? 'Crée une section d\'installation détaillée' : 'Create a detailed installation section',
                        ] as $example)
                        <button wire:click="$set('userPrompt', '{{ $example }}')"
                                class="text-left px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 transition-colors font-medium">
                            "{{ $example }}"
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                @else
                @foreach($conversation as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    @if($message['role'] === 'assistant')
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shrink-0 mr-2.5 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18"/>
                        </svg>
                    </div>
                    @endif
                    <div class="max-w-[80%] lg:max-w-[70%]">
                        <div class="{{ $message['role'] === 'user'
                                ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-md px-4 py-3'
                                : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-md px-4 py-3 shadow-sm' }}">
                            @if($message['role'] === 'assistant')
                                <div class="prose prose-sm max-w-none prose-pre:bg-slate-900 prose-pre:text-slate-100 prose-code:text-indigo-600 prose-code:bg-indigo-50 prose-code:px-1 prose-code:rounded">
                                    {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                </div>
                            @else
                                <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                            @endif
                        </div>
                    </div>
                    @if($message['role'] === 'user')
                    <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center shrink-0 ml-2.5 mt-0.5 text-xs font-black text-slate-600">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Loading indicator --}}
                @if($isLoading)
                <div class="flex justify-start">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shrink-0 mr-2.5 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-md px-4 py-3 shadow-sm">
                        <div class="flex gap-1 items-center">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
                @endif
                @endif
            </div>

            {{-- Message input --}}
            <div class="bg-white border-t border-slate-200 px-4 py-4 shrink-0">
                <form wire:submit="sendMessage" class="flex gap-3">
                    <div class="flex-1 relative">
                        <textarea wire:model="userPrompt"
                                  rows="1"
                                  {{ $providers->isEmpty() || !$selectedProviderId ? 'disabled' : '' }}
                                  placeholder="{{ $providers->isEmpty() ? (app()->getLocale() === 'fr' ? 'Aucun fournisseur IA actif...' : 'No AI provider enabled...') : (app()->getLocale() === 'fr' ? 'Écrivez votre message...' : 'Type your message...') }}"
                                  class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
                                  style="min-height: 48px; max-height: 160px;"
                                  x-data
                                  x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'"
                                  @keydown.enter.prevent="if (!$event.shiftKey) { $wire.set('userPrompt', $el.value).then(() => $wire.sendMessage()) }"></textarea>
                    </div>
                    <button type="submit"
                            :disabled="{{ ($isLoading || $providers->isEmpty() || !$selectedProviderId) ? 'true' : 'false' }}"
                            class="self-end px-4 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl transition-colors shrink-0">
                        @if($isLoading)
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        @endif
                    </button>
                </form>
                <p class="text-xs text-slate-400 mt-2 text-center">
                    {{ app()->getLocale() === 'fr' ? 'Entrée pour envoyer, Maj+Entrée pour nouvelle ligne.' : 'Enter to send, Shift+Enter for new line.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Save as document modal --}}
    @if($showSaveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showSaveModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-bold text-slate-800 text-lg mb-4">
                {{ app()->getLocale() === 'fr' ? 'Sauvegarder comme document' : 'Save as Document' }}
            </h3>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ app()->getLocale() === 'fr' ? 'Titre du document' : 'Document Title' }}
                </label>
                <input wire:model="saveTitle" type="text"
                       class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="flex gap-3">
                <button wire:click="saveAsDocument"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Sauvegarder' : 'Save' }}
                </button>
                <button wire:click="$set('showSaveModal', false)"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
