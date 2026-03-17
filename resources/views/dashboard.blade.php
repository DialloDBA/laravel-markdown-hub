<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-bold text-slate-800">
            {{ app()->getLocale() === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
        </h1>
    </x-slot>

    @php
        $user          = auth()->user();
        $totalFiles    = $user->readmeFiles()->count();
        $totalFolders  = $user->folders()->count();
        $lastFile      = $user->readmeFiles()->latest('updated_at')->first();
        $recentFiles   = $user->readmeFiles()->latest()->take(5)->get();
    @endphp

    <div class="p-4 sm:p-6 space-y-6">

        {{-- Welcome banner --}}
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-xl font-black mb-1">
                        {{ app()->getLocale() === 'fr'
                            ? 'Bonjour, ' . $user->name . ' 👋'
                            : 'Welcome back, ' . $user->name . '!' }}
                    </h2>
                    <p class="text-indigo-100 text-sm">
                        {{ app()->getLocale() === 'fr'
                            ? 'Gérez vos fichiers README et utilisez l\'assistant IA pour les améliorer.'
                            : 'Manage your README files and use the AI assistant to improve them.' }}
                    </p>
                </div>
                <a href="{{ route('import') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold rounded-xl transition-colors backdrop-blur-sm border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ app()->getLocale() === 'fr' ? 'Nouveau fichier' : 'New File' }}
                </a>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ app()->getLocale() === 'fr' ? 'Fichiers README' : 'README Files' }}
                    </p>
                    <p class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalFiles }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ app()->getLocale() === 'fr' ? 'Dossiers' : 'Folders' }}
                    </p>
                    <p class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalFolders }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ app()->getLocale() === 'fr' ? 'Dernière activité' : 'Last Activity' }}
                    </p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">
                        {{ $lastFile ? $lastFile->updated_at->diffForHumans() : (app()->getLocale() === 'fr' ? 'Aucune' : 'None') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Quick action cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('import') }}" wire:navigate
               class="bg-white rounded-2xl border border-slate-200 p-5 text-center hover:border-indigo-300 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center mx-auto mb-3 transition-colors">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">
                    {{ app()->getLocale() === 'fr' ? 'Importer' : 'Import Files' }}
                </p>
            </a>

            <a href="{{ route('import') }}" wire:navigate
               class="bg-white rounded-2xl border border-slate-200 p-5 text-center hover:border-emerald-300 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center mx-auto mb-3 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">
                    {{ app()->getLocale() === 'fr' ? 'Nouveau doc' : 'New Document' }}
                </p>
            </a>

            <a href="{{ route('ai-assistant') }}" wire:navigate
               class="bg-white rounded-2xl border border-slate-200 p-5 text-center hover:border-blue-300 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center mx-auto mb-3 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">
                    {{ app()->getLocale() === 'fr' ? 'Assistant IA' : 'AI Assistant' }}
                </p>
            </a>

            @if($lastFile)
            <a href="{{ route('readme.pdf', $lastFile) }}"
               class="bg-white rounded-2xl border border-slate-200 p-5 text-center hover:border-rose-300 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-rose-50 group-hover:bg-rose-100 flex items-center justify-center mx-auto mb-3 transition-colors">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">
                    {{ app()->getLocale() === 'fr' ? 'Export PDF' : 'Export PDF' }}
                </p>
            </a>
            @else
            <div class="bg-white rounded-2xl border border-slate-200 border-dashed p-5 text-center opacity-50 cursor-not-allowed">
                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-500">Export PDF</p>
            </div>
            @endif
        </div>

        {{-- Recent files --}}
        @if($recentFiles->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Fichiers récents' : 'Recent Files' }}
                </h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($recentFiles as $file)
                <div class="px-6 py-3.5 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $file->name }}</p>
                        <p class="text-xs text-slate-400">{{ $file->updated_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('readme.pdf', $file) }}"
                       class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                       title="Export PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- File manager --}}
        <div>
            <livewire:file-manager />
        </div>

    </div>
</x-app-layout>
