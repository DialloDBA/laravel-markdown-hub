<?php

use Livewire\Volt\Component;
use App\Models\Folder;

new class extends Component {

    public $folders = [];

    public function mount()
    {
        $this->folders = Folder::where('user_id', auth()->id())->get();
    }

    public function rendering($view)
    {
        $view->layout('layouts.app');
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-xl font-black text-slate-900">
                {{ app()->getLocale() === 'fr' ? 'Importer des fichiers' : 'Import Files' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ app()->getLocale() === 'fr'
                    ? 'Importez vos fichiers README (.md, .txt) dans votre espace.'
                    : 'Import your README files (.md, .txt) into your workspace.' }}
            </p>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Standard HTML form — enctype required for file uploads --}}
        <form action="{{ route('import.store') }}"
              method="POST"
              enctype="multipart/form-data"
              x-data="{ count: 0, mergeOn: false, sortOrder: 'name' }"
              class="space-y-5">
            @csrf

            {{-- File input --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    {{ app()->getLocale() === 'fr' ? '1. Sélectionner les fichiers' : '1. Select files' }}
                    <span class="text-slate-400 font-normal">(.md, .txt, .markdown)</span>
                </label>

                <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center
                            hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors cursor-pointer"
                     @click="$refs.fileInput.click()">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <p class="text-sm font-semibold text-slate-600 mb-1">
                        {{ app()->getLocale() === 'fr' ? 'Cliquez pour parcourir' : 'Click to browse' }}
                    </p>
                    <p class="text-xs text-slate-400">{{ app()->getLocale() === 'fr' ? 'max 10 Mo par fichier' : 'max 10 MB per file' }}</p>

                    <p x-show="count > 0"
                       x-text="count + ' {{ app()->getLocale() === 'fr' ? 'fichier(s) sélectionné(s)' : 'file(s) selected' }}'"
                       class="mt-3 text-sm font-semibold text-indigo-600"></p>

                    <input x-ref="fileInput"
                           type="file"
                           name="importFiles[]"
                           multiple
                           accept=".md,.txt,.markdown"
                           class="hidden"
                           @change="count = $event.target.files.length">
                </div>
            </div>

            {{-- Folder --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    {{ app()->getLocale() === 'fr' ? '2. Dossier de destination' : '2. Destination folder' }}
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">
                            {{ app()->getLocale() === 'fr' ? 'Dossier existant' : 'Existing folder' }}
                        </label>
                        <select name="importToFolderId"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">{{ app()->getLocale() === 'fr' ? '— Racine —' : '— Root —' }}</option>
                            @foreach($folders as $folder)
                            <option value="{{ $folder->id }}" {{ old('importToFolderId') == $folder->id ? 'selected' : '' }}>
                                {{ $folder->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">
                            {{ app()->getLocale() === 'fr' ? 'Ou créer un nouveau dossier' : 'Or create new folder' }}
                        </label>
                        <input type="text"
                               name="importNewFolderName"
                               value="{{ old('importNewFolderName') }}"
                               placeholder="{{ app()->getLocale() === 'fr' ? 'Nom du dossier...' : 'Folder name...' }}"
                               class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Merge option --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    {{ app()->getLocale() === 'fr' ? '3. Options' : '3. Options' }}
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox"
                           name="mergeOnImport"
                           value="1"
                           x-model="mergeOn"
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-slate-700">
                        {{ app()->getLocale() === 'fr' ? 'Fusionner tous les fichiers en un seul document' : 'Merge all files into a single document' }}
                    </span>
                </label>

                <div x-show="mergeOn" x-transition class="mt-4 pl-7 space-y-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ app()->getLocale() === 'fr' ? 'Ordre de fusion' : 'Merge order' }}
                    </p>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mergeSortOrder" value="name" checked
                               class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ app()->getLocale() === 'fr' ? 'Alphabétique (A–Z)' : 'Alphabetical (A–Z)' }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mergeSortOrder" value="date"
                               class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ app()->getLocale() === 'fr' ? 'Date de modification' : 'Modification date' }}</span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ app()->getLocale() === 'fr' ? 'Importer' : 'Import' }}
                </button>
            </div>

        </form>
    </div>
</div>
