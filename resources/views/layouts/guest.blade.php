<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MarkdownHub') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .input-field {
            width: 100%;
            padding: 0.65rem 0.875rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            color: #0f172a;
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .input-field:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }
        .input-field::placeholder { color: #94a3b8; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 selection:bg-indigo-500 selection:text-white">

<div class="min-h-screen flex">

    {{-- ── PANNEAU GAUCHE (décoratif) ── --}}
    <div class="hidden lg:flex lg:w-[45%] xl:w-[42%] flex-col justify-between bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-12 relative overflow-hidden">

        <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 [background-image:radial-gradient(circle,rgba(99,102,241,0.08)_1px,transparent_1px)] [background-size:36px_36px] pointer-events-none"></div>

        {{-- Logo --}}
        <a href="/" class="relative flex items-center gap-3 group w-fit">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-900/50 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-xl font-black text-white tracking-tight">
                Markdown<span class="text-indigo-400">Hub</span>
            </span>
        </a>

        {{-- Contenu central --}}
        <div class="relative">
            <h2 class="text-3xl xl:text-4xl font-black text-white leading-tight mb-4">
                {{ __('auth.panel_title') }}
            </h2>
            <p class="text-slate-400 text-base leading-relaxed mb-10">
                {{ __('auth.panel_subtitle') }}
            </p>

            <ul class="space-y-5">
                @foreach(__('auth.panel_features') as $feat)
                <li class="flex items-center gap-4">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600/25 border border-indigo-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feat['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-300">{{ $feat['label'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-slate-600">&copy; {{ date('Y') }} MarkdownHub</p>
    </div>

    {{-- ── PANNEAU DROIT (formulaire) ── --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- Barre supérieure --}}
        <div class="flex items-center justify-between px-6 sm:px-10 py-4 border-b border-slate-200 bg-white">

            {{-- Logo mobile --}}
            <a href="/" class="flex items-center gap-2 lg:hidden group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="font-black text-slate-900">Markdown<span class="text-indigo-600">Hub</span></span>
            </a>
            <div class="hidden lg:block"></div>

            {{-- Switcher + retour --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1 bg-slate-100 rounded-full p-1">
                    <a href="{{ route('lang.switch', 'fr') }}"
                       class="text-xs font-bold px-3 py-1.5 rounded-full transition-all {{ app()->getLocale() === 'fr' ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:text-slate-700' }}">FR</a>
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="text-xs font-bold px-3 py-1.5 rounded-full transition-all {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:text-slate-700' }}">EN</a>
                </div>
                <a href="/" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('auth.back_home') }}
                </a>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="flex-1 flex items-center justify-center px-6 sm:px-10 py-12 bg-white">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 sm:px-10 py-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-400">
                &copy; {{ date('Y') }} MarkdownHub &mdash; {{ __('auth.all_rights') }}
            </p>
        </div>
    </div>

</div>
</body>
</html>
