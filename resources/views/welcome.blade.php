<!DOCTYPE html>
<html lang="fr" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MarkdownHub — Gestionnaire Markdown Professionnel</title>
    <meta name="description" content="La plateforme professionnelle pour organiser, fusionner, prévisualiser et exporter vos fichiers Markdown.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }

        .text-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .blob {
            position: absolute;
            border-radius: 999px;
            filter: blur(80px);
            opacity: 0.18;
            pointer-events: none;
        }

        /* Feature card icon dual state */
        .feature-card .icon-dark { display: flex; }
        .feature-card .icon-blue { display: none; }
        .feature-card:hover .icon-dark { display: none; }
        .feature-card:hover .icon-blue { display: flex; }

        /* FAQ accordion */
        .faq-body { max-height: 0; overflow: hidden; transition: max-height 0.35s ease; }
        .faq-body.open { max-height: 300px; }
        .faq-icon { transition: transform 0.3s ease; }
        .faq-item.open .faq-icon { transform: rotate(45deg); }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Lang switcher */
        .lang-btn { transition: all 0.2s ease; }
        .lang-btn.active {
            background: #4f46e5;
            color: white;
        }

        /* Hide/show by lang */
        [data-lang="en"] { display: none; }
        body.lang-en [data-lang="en"] { display: revert; }
        body.lang-en [data-lang="fr"] { display: none; }
    </style>
</head>
<body class="antialiased font-sans bg-white text-slate-900 selection:bg-indigo-500 selection:text-white">

{{-- ══════════════════════ NAVBAR ══════════════════════ --}}
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-100 shadow-sm shadow-slate-100/60">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">
        <div class="flex items-center justify-between h-[70px]">

            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 shrink-0 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-300/50 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-[1.2rem] font-black tracking-tight text-slate-900">
                    Markdown<span class="text-indigo-600">Hub</span>
                </span>
            </a>

            {{-- Nav links --}}
            <ul class="hidden md:flex items-center gap-8 text-[0.875rem] font-semibold text-slate-600">
                <li>
                    <a href="#features" class="hover:text-indigo-600 transition-colors">
                        <span data-lang="fr">Fonctionnalités</span>
                        <span data-lang="en">Features</span>
                    </a>
                </li>
                <li>
                    <a href="#how-it-works" class="hover:text-indigo-600 transition-colors">
                        <span data-lang="fr">Comment ça marche</span>
                        <span data-lang="en">How it works</span>
                    </a>
                </li>
                <li>
                    <a href="#pricing" class="hover:text-indigo-600 transition-colors">
                        <span data-lang="fr">Tarifs</span>
                        <span data-lang="en">Pricing</span>
                    </a>
                </li>
                <li>
                    <a href="#faq" class="hover:text-indigo-600 transition-colors">FAQ</a>
                </li>
            </ul>

            {{-- Right: lang switcher + auth --}}
            <div class="flex items-center gap-4">

                {{-- Language switcher --}}
                <div class="flex items-center gap-1 bg-slate-100 rounded-full p-1">
                    <button onclick="setLang('fr')" id="btn-fr"
                            class="lang-btn active text-xs font-bold px-3 py-1.5 rounded-full">
                        FR
                    </button>
                    <button onclick="setLang('en')" id="btn-en"
                            class="lang-btn text-xs font-bold px-3 py-1.5 rounded-full text-slate-500">
                        EN
                    </button>
                </div>

                {{-- Auth --}}
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5">
                        <span data-lang="fr">Tableau de bord →</span>
                        <span data-lang="en">Dashboard →</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors hidden sm:inline">
                        <span data-lang="fr">Connexion</span>
                        <span data-lang="en">Sign in</span>
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 rounded-full shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5">
                            <span data-lang="fr">S'inscrire gratuitement</span>
                            <span data-lang="en">Sign up free</span>
                        </a>
                    @endif
                @endauth
            </div>

        </div>
    </div>
</nav>


{{-- ══════════════════════ HERO ══════════════════════ --}}
<section class="relative overflow-hidden bg-white pt-20 pb-16 lg:pt-28 lg:pb-24">
    <div class="blob w-[600px] h-[600px] bg-indigo-400 -top-48 -right-48"></div>
    <div class="blob w-[400px] h-[400px] bg-blue-300 top-32 -left-32"></div>
    <div class="pointer-events-none absolute inset-0 [background-image:radial-gradient(circle,#c7d2fe_1px,transparent_1px)] [background-size:40px_40px] opacity-40"></div>

    <div class="relative max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

            {{-- Texte --}}
            <div class="flex-1 text-center lg:text-left">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold px-4 py-1.5 rounded-full mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span data-lang="fr">v1.0 — Disponible maintenant · Propulsé par Laravel</span>
                    <span data-lang="en">v1.0 — Now Available · Powered by Laravel</span>
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-[4.25rem] font-black leading-[1.07] tracking-tight text-slate-900 mb-6">
                    <span data-lang="fr">Gérez vos fichiers<br><span class="text-gradient">Markdown</span><br>comme un pro.</span>
                    <span data-lang="en">Manage your<br><span class="text-gradient">Markdown files</span><br>like a pro.</span>
                </h1>

                <p class="text-lg text-slate-500 leading-relaxed max-w-lg mx-auto lg:mx-0 mb-8">
                    <span data-lang="fr">MarkdownHub est l'espace de travail tout-en-un pour importer, organiser, éditer, fusionner et exporter votre documentation Markdown — facilement et élégamment.</span>
                    <span data-lang="en">MarkdownHub is the all-in-one workspace to import, organize, edit, merge and export your Markdown documentation — beautifully and effortlessly.</span>
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-2xl text-base shadow-xl shadow-indigo-300/40 transition-all hover:-translate-y-1">
                        <span data-lang="fr">Commencer gratuitement</span>
                        <span data-lang="en">Get started — it's free</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a href="#how-it-works"
                       class="inline-flex items-center justify-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold px-8 py-4 rounded-2xl text-base transition-all hover:-translate-y-1">
                        <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span data-lang="fr">Voir comment ça marche</span>
                        <span data-lang="en">See how it works</span>
                    </a>
                </div>

                <p class="mt-5 text-xs text-slate-400 font-medium">
                    <span data-lang="fr">Sans carte bancaire · Gratuit à vie · Jusqu'à 50 fichiers/lot</span>
                    <span data-lang="en">No credit card · Free forever · Up to 50 files/batch</span>
                </p>
            </div>

            {{-- Mock App Window --}}
            <div class="flex-1 w-full max-w-2xl lg:max-w-none">
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-br from-indigo-200/40 to-blue-200/30 rounded-3xl blur-2xl"></div>
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-2xl shadow-slate-300/40 bg-[#1e1e2e]">
                        <div class="flex items-center gap-2 px-4 py-3 bg-[#2a2a3e] border-b border-white/5">
                            <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                            <div class="flex-1 mx-4 bg-white/8 rounded h-5 flex items-center justify-center">
                                <span class="text-white/25 text-[10px] font-mono">markdownhub.app/dashboard</span>
                            </div>
                        </div>
                        <div class="flex" style="min-height:300px;">
                            <aside class="w-44 bg-[#16162a] border-r border-white/5 p-3 shrink-0">
                                <p class="text-white/30 text-[9px] uppercase tracking-widest font-bold px-2 mb-2">
                                    <span data-lang="fr">Dossiers</span><span data-lang="en">Folders</span>
                                </p>
                                @foreach([['Frontend', true],['Backend API', false],['Design System', false],['Déploiement', false]] as [$name, $active])
                                <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[11px] mb-0.5 {{ $active ? 'bg-indigo-600/30 text-indigo-300' : 'text-white/35' }}">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    {{ $name }}
                                </div>
                                @endforeach
                                <p class="text-white/25 text-[9px] uppercase tracking-widest font-bold px-2 mt-4 mb-2">
                                    <span data-lang="fr">Fichiers</span><span data-lang="en">Files</span>
                                </p>
                                @foreach(['README.md','CONTRIBUTING.md','API.md'] as $f)
                                <div class="flex items-center gap-2 px-2 py-1.5 text-white/30 text-[11px]">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $f }}
                                </div>
                                @endforeach
                            </aside>
                            <div class="flex-1 p-5 font-mono text-[11px] leading-relaxed bg-[#1a1a2e]">
                                <p class="text-white/25 text-[9px] uppercase tracking-widest mb-3">README.md</p>
                                <p class="text-indigo-400 font-bold text-sm mb-1"># Documentation Frontend</p>
                                <p class="text-white/50 mb-0.5">## Installation</p>
                                <p class="text-white/40 mb-2">Ce projet utilise <span class="text-amber-300">**React 18**</span> avec TypeScript.</p>
                                <div class="bg-white/5 border border-white/5 rounded-lg p-3 text-white/35 mb-2">
                                    <p class="text-green-400/70">src/</p>
                                    <p class="pl-4">├── components/</p>
                                    <p class="pl-4">├── hooks/</p>
                                    <p class="pl-4">└── pages/</p>
                                </div>
                                <p class="text-white/35">Lancez <span class="bg-white/10 text-emerald-300 px-1 rounded">npm install</span></p>
                            </div>
                            <div class="flex-1 p-5 border-l border-white/5 bg-[#12121f] hidden lg:block">
                                <p class="text-white/20 text-[9px] uppercase tracking-widest mb-3">
                                    <span data-lang="fr">Prévisualisation</span><span data-lang="en">Live Preview</span>
                                </p>
                                <p class="text-white font-bold text-xl mb-2">Documentation Frontend</p>
                                <p class="text-white/55 font-semibold text-sm mb-2">Installation</p>
                                <p class="text-white/35 text-xs mb-3">Ce projet utilise <span class="text-indigo-300 font-semibold">React 18</span></p>
                                <div class="bg-white/5 border border-white/5 rounded-lg p-3 font-mono text-xs text-white/35">
                                    src/<br><span class="pl-4">├── components/</span><br><span class="pl-4">└── pages/</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2 bg-indigo-900/30 border-t border-white/5">
                            <div class="flex items-center gap-3">
                                <span class="text-indigo-300/70 text-[10px] font-mono">Markdown · GFM</span>
                                <span class="text-white/20 text-[10px]">|</span>
                                <span class="text-white/30 text-[10px]">
                                    <span data-lang="fr">3 fichiers sélectionnés</span>
                                    <span data-lang="en">3 files selected</span>
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <span class="bg-emerald-500/25 text-emerald-300 text-[10px] px-2 py-0.5 rounded font-semibold">
                                    <span data-lang="fr">⊕ Fusionner</span><span data-lang="en">⊕ Merge</span>
                                </span>
                                <span class="bg-indigo-500/30 text-indigo-200 text-[10px] px-2 py-0.5 rounded font-semibold">⇣ PDF</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ══════════════════════ SOCIAL PROOF ══════════════════════ --}}
<section class="py-14 border-y border-slate-100 bg-slate-50">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">
        <p class="text-center text-xs text-slate-400 uppercase tracking-widest font-semibold mb-8">
            <span data-lang="fr">Construit avec les meilleurs outils open source</span>
            <span data-lang="en">Built with world-class open source tools</span>
        </p>
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
            @foreach([
                ['Laravel',      'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'],
                ['Livewire',     'M13 10V3L4 14h7v7l9-11h-7z'],
                ['Tailwind CSS', 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343'],
                ['Vite',         'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5'],
                ['CommonMark',   'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['DomPDF',       'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ] as [$name, $icon])
            <div class="flex items-center gap-2 text-slate-400 hover:text-indigo-500 transition-colors group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                </svg>
                <span class="text-sm font-semibold">{{ $name }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════ FEATURES ══════════════════════ --}}
<section id="features" class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">

        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">
                <span data-lang="fr">Fonctionnalités</span>
                <span data-lang="en">Features</span>
            </span>
            <h2 class="mt-3 text-4xl lg:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                <span data-lang="fr">Tout ce qu'il faut pour<br>maîtriser votre documentation</span>
                <span data-lang="en">Everything you need<br>to master your docs</span>
            </h2>
            <p class="mt-4 text-slate-500 text-lg leading-relaxed">
                <span data-lang="fr">Des outils puissants conçus pour les développeurs qui prennent la documentation au sérieux.</span>
                <span data-lang="en">Advanced tools designed for developers who take documentation seriously.</span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
            @foreach([
                [
                    'icon'  => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                    'color' => 'indigo',
                    'fr'    => ['Dossiers intelligents', 'Organisez des centaines de fichiers dans des dossiers virtuels. Catégorisez par projet, date ou priorité et retrouvez tout instantanément.'],
                    'en'    => ['Smart Folders', 'Organize hundreds of files into virtual folders. Categorize by project, date or priority and find anything instantly.'],
                ],
                [
                    'icon'  => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
                    'color' => 'blue',
                    'fr'    => ['Fusion en un clic', 'Combinez plusieurs fichiers Markdown en un seul document soigné. Parfait pour les livrets de projet et les notes de version.'],
                    'en'    => ['One-Click Merge', 'Combine multiple Markdown files into one polished document. Perfect for project books and release notes.'],
                ],
                [
                    'icon'  => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                    'color' => 'violet',
                    'fr'    => ['Export PDF professionnel', 'Exportez vos documents en PDF avec une typographie professionnelle. Blocs de code et tableaux parfaitement mis en page.'],
                    'en'    => ['Pro PDF Export', 'Export documents to beautifully styled PDFs. Code blocks and tables are pixel-perfect on paper.'],
                ],
                [
                    'icon'  => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
                    'color' => 'emerald',
                    'fr'    => ['Import en masse', 'Importez jusqu\'à 50 fichiers à la fois par glisser-déposer. Triez par nom ou par date — MarkdownHub fait le reste.'],
                    'en'    => ['Batch Import', 'Import up to 50 files at once with drag-and-drop. Sort by name or date — MarkdownHub handles the rest.'],
                ],
                [
                    'icon'  => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
                    'color' => 'amber',
                    'fr'    => ['Prévisualisation en direct', 'Éditez en Markdown avec un aperçu rendu en temps réel côte à côte. Support complet du GFM.'],
                    'en'    => ['Live Preview', 'Edit Markdown side-by-side with a real-time rendered preview. Full GitHub Flavored Markdown support.'],
                ],
                [
                    'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'color' => 'rose',
                    'fr'    => ['Stockage sécurisé', 'Vos fichiers sont privés par défaut. Construit sur Laravel avec une authentification standard et une protection des données.'],
                    'en'    => ['Secure Storage', 'All your files are private by default. Built on Laravel with industry-standard auth and data protection.'],
                ],
            ] as $feature)
            @php
                $map = [
                    'indigo'  => ['light'=>'bg-indigo-50',  'border'=>'border-indigo-100'],
                    'blue'    => ['light'=>'bg-blue-50',    'border'=>'border-blue-100'],
                    'violet'  => ['light'=>'bg-violet-50',  'border'=>'border-violet-100'],
                    'emerald' => ['light'=>'bg-emerald-50', 'border'=>'border-emerald-100'],
                    'amber'   => ['light'=>'bg-amber-50',   'border'=>'border-amber-100'],
                    'rose'    => ['light'=>'bg-rose-50',    'border'=>'border-rose-100'],
                ];
                $c = $map[$feature['color']];
            @endphp
            <div class="feature-card group reveal p-8 rounded-2xl border border-slate-100 bg-white hover:bg-slate-50 shadow-sm hover:shadow-xl hover:shadow-slate-200/60 transition-all duration-300 cursor-default">
                <div class="icon-dark w-12 h-12 rounded-xl {{ $c['light'] }} border {{ $c['border'] }} items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-slate-700 m-auto mt-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <div class="icon-blue w-12 h-12 rounded-xl bg-indigo-600 items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white m-auto mt-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-indigo-700 transition-colors">
                    <span data-lang="fr">{{ $feature['fr'][0] }}</span>
                    <span data-lang="en">{{ $feature['en'][0] }}</span>
                </h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    <span data-lang="fr">{{ $feature['fr'][1] }}</span>
                    <span data-lang="en">{{ $feature['en'][1] }}</span>
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════ ALTERNATING BLOCKS ══════════════════════ --}}
<section class="py-24 bg-slate-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10 space-y-28">

        {{-- Block 1 --}}
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20 reveal">
            <div class="flex-1 w-full">
                <div class="relative">
                    <div class="absolute -inset-3 bg-gradient-to-br from-indigo-100 to-blue-100 rounded-3xl blur-xl opacity-60"></div>
                    <div class="relative bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        </div>
                        <div class="space-y-2.5">
                            @foreach([['README.md','2.4 KB','indigo'],['CONTRIBUTING.md','1.1 KB','blue'],['API-Reference.md','8.7 KB','violet'],['CHANGELOG.md','4.2 KB','emerald'],['DEPLOYMENT.md','3.0 KB','amber']] as [$name,$size,$color])
                            @php $dot=['indigo'=>'bg-indigo-500','blue'=>'bg-blue-500','violet'=>'bg-violet-500','emerald'=>'bg-emerald-500','amber'=>'bg-amber-500']; @endphp
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full {{ $dot[$color] }}"></span>
                                    <span class="text-sm font-semibold text-slate-700 group-hover:text-indigo-700">{{ $name }}</span>
                                </div>
                                <span class="text-xs text-slate-400">{{ $size }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex gap-2">
                            <div class="flex-1 bg-indigo-600 text-white text-xs font-bold py-2.5 rounded-xl text-center">
                                <span data-lang="fr">⊕ Fusionner</span><span data-lang="en">⊕ Merge selected</span>
                            </div>
                            <div class="flex-1 border border-slate-200 text-slate-600 text-xs font-bold py-2.5 rounded-xl text-center">⇣ Export PDF</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">
                    <span data-lang="fr">Import & Organisation</span>
                    <span data-lang="en">Import & Organize</span>
                </span>
                <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900 leading-tight mb-5">
                    <span data-lang="fr">Centralisez tous vos<br>fichiers en un seul endroit.</span>
                    <span data-lang="en">Bring all your files<br>under one roof.</span>
                </h2>
                <p class="text-slate-500 leading-relaxed mb-6">
                    <span data-lang="fr">Arrêtez de chercher vos fichiers Markdown éparpillés dans vos projets. MarkdownHub vous permet d'importer jusqu'à 50 fichiers en une fois, de les trier automatiquement et de les placer dans des dossiers organisés — en quelques secondes.</span>
                    <span data-lang="en">Stop hunting for scattered Markdown files across your projects. MarkdownHub lets you import up to 50 files at once, sort them automatically, and place them into organized folders — all in seconds.</span>
                </p>
                <ul class="space-y-3">
                    @foreach([
                        ['fr' => 'Glisser-déposer pour import en masse (.md & .txt)', 'en' => 'Drag-and-drop batch upload (.md & .txt)'],
                        ['fr' => 'Tri automatique par nom ou date de modification',    'en' => 'Auto-sort by filename or last modified date'],
                        ['fr' => 'Créer des dossiers à l\'import ou après',           'en' => 'Create folders on import or organize later'],
                        ['fr' => 'Recherche instantanée dans tous vos documents',     'en' => 'Instant search across all your documents'],
                    ] as $point)
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span data-lang="fr">{{ $point['fr'] }}</span>
                        <span data-lang="en">{{ $point['en'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Block 2 --}}
        <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20 reveal">
            <div class="flex-1 w-full">
                <div class="relative">
                    <div class="absolute -inset-3 bg-gradient-to-br from-violet-100 to-pink-100 rounded-3xl blur-xl opacity-60"></div>
                    <div class="relative bg-[#1e1e2e] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 bg-[#2a2a3e] border-b border-white/5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500/70"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-500/70"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500/70"></span>
                            <span class="ml-3 text-white/25 text-[10px] font-mono">merged-output.pdf</span>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="h-7 bg-white/10 rounded-lg w-3/4"></div>
                            <div class="h-3 bg-white/6 rounded w-full"></div>
                            <div class="h-3 bg-white/6 rounded w-5/6"></div>
                            <div class="h-3 bg-white/6 rounded w-4/6"></div>
                            <div class="h-4 bg-indigo-500/30 rounded w-1/3 mt-4"></div>
                            <div class="h-3 bg-white/5 rounded w-full"></div>
                            <div class="h-3 bg-white/5 rounded w-full"></div>
                            <div class="bg-white/5 rounded-lg p-3 space-y-1.5 border border-white/5">
                                <div class="h-2.5 bg-green-400/30 rounded w-1/4"></div>
                                <div class="h-2 bg-white/10 rounded w-2/3"></div>
                                <div class="h-2 bg-white/10 rounded w-1/2"></div>
                                <div class="h-2 bg-white/10 rounded w-3/4"></div>
                            </div>
                            <div class="h-3 bg-white/5 rounded w-4/5"></div>
                        </div>
                        <div class="px-6 py-3 bg-violet-900/30 border-t border-white/5 flex justify-between items-center">
                            <span class="text-violet-300 text-[10px] font-mono">
                                <span data-lang="fr">5 fichiers fusionnés · 12 pages</span>
                                <span data-lang="en">5 files merged · 12 pages</span>
                            </span>
                            <span class="bg-violet-500/30 text-violet-200 text-[10px] px-2 py-0.5 rounded font-semibold">
                                <span data-lang="fr">Télécharger PDF</span>
                                <span data-lang="en">Download PDF</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <span class="text-xs font-bold text-violet-600 uppercase tracking-widest">
                    <span data-lang="fr">Fusion & Export</span>
                    <span data-lang="en">Merge & Export</span>
                </span>
                <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900 leading-tight mb-5">
                    <span data-lang="fr">Fusionnez, puis exportez<br>en un seul clic.</span>
                    <span data-lang="en">Merge, then export<br>in one click.</span>
                </h2>
                <p class="text-slate-500 leading-relaxed mb-6">
                    <span data-lang="fr">Sélectionnez plusieurs fichiers, fusionnez-les en un document structuré, puis exportez un PDF au style professionnel — parfait pour le partage avec vos clients ou votre équipe.</span>
                    <span data-lang="en">Select any number of files, merge them into a single structured document, then export a professionally styled PDF — perfect for sharing with clients or teams.</span>
                </p>
                <ul class="space-y-3">
                    @foreach([
                        ['fr' => 'Sélectionnez et fusionnez dans n\'importe quel ordre', 'en' => 'Select & merge multiple files in any order'],
                        ['fr' => 'En-têtes de section et séparateurs automatiques',       'en' => 'Automatic section headings and dividers'],
                        ['fr' => 'Blocs de code optimisés pour l\'impression',           'en' => 'Code blocks scroll-optimized for print'],
                        ['fr' => 'Tableaux et images parfaitement formatés',             'en' => 'Tables and images perfectly formatted'],
                    ] as $point)
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <svg class="w-5 h-5 text-violet-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span data-lang="fr">{{ $point['fr'] }}</span>
                        <span data-lang="en">{{ $point['en'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</section>


{{-- ══════════════════════ HOW IT WORKS ══════════════════════ --}}
<section id="how-it-works" class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">

        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">
                <span data-lang="fr">Fonctionnement</span>
                <span data-lang="en">Workflow</span>
            </span>
            <h2 class="mt-3 text-4xl lg:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                <span data-lang="fr">Opérationnel en<br>3 étapes simples</span>
                <span data-lang="en">Up and running<br>in 3 simple steps</span>
            </h2>
            <p class="mt-4 text-slate-500 text-lg leading-relaxed">
                <span data-lang="fr">De zéro à un espace de documentation parfaitement organisé en quelques minutes.</span>
                <span data-lang="en">From zero to a perfectly organized documentation workspace in minutes.</span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="hidden md:block absolute top-14 left-[calc(33%+28px)] right-[calc(33%+28px)] h-px bg-gradient-to-r from-indigo-200 via-blue-200 to-violet-200"></div>

            @foreach([
                [
                    'num'  => '01',
                    'grad' => 'from-indigo-600 to-blue-600',
                    'shad' => 'shadow-indigo-200',
                    'fr'   => ['Créer un compte', 'Inscrivez-vous en quelques secondes — aucune carte bancaire requise. Votre espace de travail privé est prêt instantanément.'],
                    'en'   => ['Create an account', 'Sign up in seconds — no credit card needed. Your private workspace is ready instantly.'],
                ],
                [
                    'num'  => '02',
                    'grad' => 'from-blue-600 to-violet-600',
                    'shad' => 'shadow-blue-200',
                    'fr'   => ['Importez vos fichiers', 'Chargez jusqu\'à 50 fichiers Markdown à la fois. Créez des dossiers et laissez MarkdownHub tout organiser.'],
                    'en'   => ['Import your files', 'Upload up to 50 Markdown files at once. Create folders and let MarkdownHub organise everything.'],
                ],
                [
                    'num'  => '03',
                    'grad' => 'from-violet-600 to-pink-600',
                    'shad' => 'shadow-violet-200',
                    'fr'   => ['Exportez & partagez', 'Fusionnez, prévisualisez le résultat, puis téléchargez un beau PDF prêt pour votre équipe ou vos clients.'],
                    'en'   => ['Export & share', 'Merge files, preview the result, then download a beautiful PDF ready for your team or clients.'],
                ],
            ] as $step)
            <div class="reveal relative text-center group">
                <div class="w-28 h-28 mx-auto mb-6 rounded-2xl bg-gradient-to-br {{ $step['grad'] }} flex items-center justify-center shadow-2xl {{ $step['shad'] }} group-hover:scale-105 transition-transform duration-300">
                    <span class="text-white font-black text-4xl">{{ $step['num'] }}</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">
                    <span data-lang="fr">{{ $step['fr'][0] }}</span>
                    <span data-lang="en">{{ $step['en'][0] }}</span>
                </h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xs mx-auto">
                    <span data-lang="fr">{{ $step['fr'][1] }}</span>
                    <span data-lang="en">{{ $step['en'][1] }}</span>
                </p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-14 reveal">
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-2xl shadow-xl shadow-indigo-300/40 transition-all hover:-translate-y-1">
                <span data-lang="fr">Commencer gratuitement dès aujourd'hui</span>
                <span data-lang="en">Start for free today</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

    </div>
</section>


{{-- ══════════════════════ PRICING ══════════════════════ --}}
<section id="pricing" class="py-24 lg:py-32 bg-slate-50">
    <div class="max-w-5xl mx-auto px-5 sm:px-8 lg:px-10">

        <div class="text-center max-w-xl mx-auto mb-16 reveal">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">
                <span data-lang="fr">Tarifs</span>
                <span data-lang="en">Pricing</span>
            </span>
            <h2 class="mt-3 text-4xl lg:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                <span data-lang="fr">Tarifs simples et transparents</span>
                <span data-lang="en">Simple, transparent pricing</span>
            </h2>
            <p class="mt-4 text-slate-500 text-lg">
                <span data-lang="fr">Sans frais cachés. Aucune carte bancaire requise pour démarrer.</span>
                <span data-lang="en">No hidden fees. No credit card required to start.</span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-7">

            {{-- Free --}}
            <div class="reveal bg-white rounded-2xl border border-slate-200 p-9 shadow-sm hover:shadow-xl hover:shadow-slate-200/60 transition-all duration-300">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
                    <span data-lang="fr">Gratuit</span>
                    <span data-lang="en">Free</span>
                </div>
                <div class="text-5xl font-black text-slate-900 mb-1">$0</div>
                <p class="text-slate-400 text-sm mb-7">
                    <span data-lang="fr">Gratuit pour toujours. Aucune limite de fichiers.</span>
                    <span data-lang="en">Forever free. No limits on files.</span>
                </p>
                <ul class="space-y-3 mb-8">
                    @foreach([
                        ['fr' => 'Fichiers & dossiers illimités',             'en' => 'Unlimited files & folders'],
                        ['fr' => 'Import en masse — jusqu\'à 50 fichiers',   'en' => 'Batch import — up to 50 files'],
                        ['fr' => 'Prévisualisation Markdown (GFM)',           'en' => 'GitHub Flavored Markdown preview'],
                        ['fr' => 'Fusion en un clic',                        'en' => 'One-click merge'],
                        ['fr' => 'Export PDF',                               'en' => 'PDF export'],
                        ['fr' => 'Stockage privé sécurisé',                  'en' => 'Secure private storage'],
                    ] as $item)
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span data-lang="fr">{{ $item['fr'] }}</span>
                        <span data-lang="en">{{ $item['en'] }}</span>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}"
                   class="block text-center border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold py-3.5 rounded-xl transition-all duration-200 text-sm">
                    <span data-lang="fr">Commencer gratuitement</span>
                    <span data-lang="en">Get started free</span>
                </a>
            </div>

            {{-- Pro --}}
            <div class="reveal relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-700 rounded-2xl p-9 shadow-2xl shadow-indigo-500/30 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-56 h-56 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Pro</div>
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                            <span data-lang="fr">Bientôt disponible</span>
                            <span data-lang="en">Coming soon</span>
                        </span>
                    </div>
                    <div class="text-5xl font-black text-white mb-1">$9<span class="text-indigo-200 text-2xl font-semibold">/mois</span></div>
                    <p class="text-indigo-200 text-sm mb-7">
                        <span data-lang="fr">Par espace de travail · facturé mensuellement</span>
                        <span data-lang="en">Per workspace · billed monthly</span>
                    </p>
                    <ul class="space-y-3 mb-8">
                        @foreach([
                            ['fr' => 'Tout ce qui est inclus dans Gratuit', 'en' => 'Everything in Free'],
                            ['fr' => 'Collaboration en équipe',             'en' => 'Team collaboration'],
                            ['fr' => 'Historique des versions',             'en' => 'Version history'],
                            ['fr' => 'Thèmes d\'export personnalisés',     'en' => 'Custom export themes'],
                            ['fr' => 'Support prioritaire',                 'en' => 'Priority support'],
                            ['fr' => 'Accès API',                          'en' => 'API access'],
                        ] as $item)
                        <li class="flex items-center gap-3 text-sm text-indigo-50">
                            <svg class="w-4 h-4 text-indigo-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span data-lang="fr">{{ $item['fr'] }}</span>
                            <span data-lang="en">{{ $item['en'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <div class="block text-center bg-white/15 border border-white/20 text-white/60 font-bold py-3.5 rounded-xl text-sm cursor-not-allowed select-none">
                        <span data-lang="fr">Me notifier au lancement</span>
                        <span data-lang="en">Notify me on launch</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ══════════════════════ FAQ ══════════════════════ --}}
<section id="faq" class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-5 sm:px-8 lg:px-10">

        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">FAQ</span>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900">
                <span data-lang="fr">Questions fréquentes</span>
                <span data-lang="en">Frequently asked questions</span>
            </h2>
        </div>

        <div class="space-y-3 reveal">
            @foreach([
                [
                    'fr' => ['Quels formats de fichiers sont supportés ?', 'MarkdownHub prend en charge les fichiers Markdown (.md) et texte brut (.txt). Le contenu est interprété avec GitHub Flavored Markdown (GFM), incluant les tableaux, blocs de code, listes de tâches et plus encore.'],
                    'en' => ['What file formats does MarkdownHub support?', 'MarkdownHub supports Markdown (.md) and plain text (.txt) files. Content is parsed using GitHub Flavored Markdown (GFM) which includes tables, code blocks, task lists and more.'],
                ],
                [
                    'fr' => ['Combien de fichiers puis-je importer à la fois ?', 'Vous pouvez importer jusqu\'à 50 fichiers en un seul lot. Il n\'y a aucune limite sur le nombre total de fichiers stockés — créez autant de dossiers et de fichiers que votre projet le nécessite.'],
                    'en' => ['How many files can I import at once?', 'You can import up to 50 files in a single batch. There is no limit on the total number of files you can store — create as many folders and files as your project needs.'],
                ],
                [
                    'fr' => ['Puis-je modifier mes fichiers dans MarkdownHub ?', 'Oui. L\'éditeur intégré vous permet de modifier votre contenu Markdown directement dans l\'application, avec une prévisualisation rendue en temps réel côte à côte.'],
                    'en' => ['Can I edit my files inside MarkdownHub?', 'Yes. The built-in editor lets you modify your markdown content directly within the app, with a live side-by-side rendered preview so you can see changes in real time.'],
                ],
                [
                    'fr' => ['Comment fonctionne l\'export PDF ?', 'MarkdownHub utilise DomPDF pour générer un PDF professionnel. Les blocs de code sont défilables horizontalement, les tableaux longs sont optimisés pour l\'impression et la typographie est soignée.'],
                    'en' => ['How does the PDF export work?', 'MarkdownHub uses DomPDF to render your document into a professional PDF. Code blocks are horizontally scrollable, long tables are optimized for print, and typography is styled for readability.'],
                ],
                [
                    'fr' => ['Mes données sont-elles privées et sécurisées ?', 'Absolument. Chaque compte est isolé — vos fichiers ne sont accessibles qu\'à vous. L\'application est construite sur Laravel avec une authentification standard et aucun partage de données avec des tiers.'],
                    'en' => ['Is my data private and secure?', 'Absolutely. Each account is isolated — your files are only accessible to you. The app is built on Laravel with standard authentication, hashed passwords, and no third-party data sharing.'],
                ],
            ] as $faq)
            <div class="faq-item border border-slate-200 rounded-2xl overflow-hidden bg-white hover:border-indigo-200 transition-colors">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between text-left px-6 py-5 font-semibold text-slate-900 hover:text-indigo-700 transition-colors">
                    <span>
                        <span data-lang="fr">{{ $faq['fr'][0] }}</span>
                        <span data-lang="en">{{ $faq['en'][0] }}</span>
                    </span>
                    <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                <div class="faq-body">
                    <p class="px-6 pb-5 text-slate-500 text-sm leading-relaxed">
                        <span data-lang="fr">{{ $faq['fr'][1] }}</span>
                        <span data-lang="en">{{ $faq['en'][1] }}</span>
                    </p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>


{{-- ══════════════════════ CTA BANNER ══════════════════════ --}}
<section class="py-24 bg-slate-50">
    <div class="max-w-5xl mx-auto px-5 sm:px-8 lg:px-10">
        <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-700 p-12 lg:p-20 text-center shadow-2xl shadow-indigo-400/20">
            <div class="pointer-events-none absolute -top-20 -right-20 w-64 h-64 rounded-full bg-white/8 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-14 -left-14 w-48 h-48 rounded-full bg-blue-400/20 blur-2xl"></div>
            <div class="relative">
                <h2 class="text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-5">
                    <span data-lang="fr">Prêt à organiser votre<br>documentation comme un pro ?</span>
                    <span data-lang="en">Ready to organize your<br>documentation like a pro?</span>
                </h2>
                <p class="text-indigo-200 text-lg max-w-xl mx-auto mb-10">
                    <span data-lang="fr">Rejoignez les développeurs qui utilisent MarkdownHub pour garder leur documentation propre, organisée et toujours prête à l'export.</span>
                    <span data-lang="en">Join developers who rely on MarkdownHub to keep their docs clean, organized and always export-ready.</span>
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 bg-white text-indigo-700 font-extrabold px-10 py-4 rounded-2xl text-base shadow-2xl hover:bg-indigo-50 transition-all hover:scale-105 active:scale-95">
                        <span data-lang="fr">Créer votre compte gratuit</span>
                        <span data-lang="en">Create your free account</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center gap-2 border border-white/30 text-white font-semibold px-10 py-4 rounded-2xl text-base hover:bg-white/10 transition-all">
                        <span data-lang="fr">Déjà un compte ?</span>
                        <span data-lang="en">Already have an account?</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ══════════════════════ FOOTER ══════════════════════ --}}
<footer class="bg-slate-900 text-slate-300">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10 pt-16 pb-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            {{-- Brand --}}
            <div class="sm:col-span-2">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-black text-white">Markdown<span class="text-indigo-400">Hub</span></span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs mb-6">
                    <span data-lang="fr">L'espace de travail professionnel pour organiser, fusionner et exporter votre documentation Markdown.</span>
                    <span data-lang="en">The professional workspace for organizing, merging, and exporting your Markdown documentation.</span>
                </p>
                <div class="flex items-center gap-3">
                    @foreach([
                        ['GitHub',   'M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z'],
                        ['Twitter',  'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                    ] as [$label, $path])
                    <a href="#" aria-label="{{ $label }}"
                       class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $path }}"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Product --}}
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-5">
                    <span data-lang="fr">Produit</span>
                    <span data-lang="en">Product</span>
                </h4>
                <ul class="space-y-3">
                    @foreach([
                        ['fr'=>'Fonctionnalités','en'=>'Features',      'href'=>'#features'],
                        ['fr'=>'Comment ça marche','en'=>'How it works','href'=>'#how-it-works'],
                        ['fr'=>'Tarifs','en'=>'Pricing',                'href'=>'#pricing'],
                        ['fr'=>'FAQ','en'=>'FAQ',                       'href'=>'#faq'],
                    ] as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                            <span data-lang="fr">{{ $link['fr'] }}</span>
                            <span data-lang="en">{{ $link['en'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-5">
                    <span data-lang="fr">Compte</span>
                    <span data-lang="en">Account</span>
                </h4>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                            <span data-lang="fr">Connexion</span>
                            <span data-lang="en">Sign in</span>
                        </a>
                    </li>
                    @if(Route::has('register'))
                    <li>
                        <a href="{{ route('register') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                            <span data-lang="fr">Créer un compte</span>
                            <span data-lang="en">Create account</span>
                        </a>
                    </li>
                    @endif
                    @auth
                    <li>
                        <a href="{{ url('/dashboard') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                            <span data-lang="fr">Tableau de bord</span>
                            <span data-lang="en">Dashboard</span>
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                &copy; {{ date('Y') }} MarkdownHub.
                <span data-lang="fr">Tous droits réservés.</span>
                <span data-lang="en">All rights reserved.</span>
            </p>
            <p class="text-xs font-mono text-slate-600">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} &middot; PHP v{{ PHP_VERSION }}
            </p>
        </div>

    </div>
</footer>


{{-- ══════════════════════ SCRIPTS ══════════════════════ --}}
<script>
    // ── Language switcher ──
    function setLang(lang) {
        document.body.classList.toggle('lang-en', lang === 'en');
        document.getElementById('html-root').setAttribute('lang', lang === 'en' ? 'en' : 'fr');
        document.getElementById('btn-fr').classList.toggle('active', lang === 'fr');
        document.getElementById('btn-en').classList.toggle('active', lang === 'en');
        document.getElementById('btn-fr').classList.toggle('text-slate-500', lang !== 'fr');
        document.getElementById('btn-en').classList.toggle('text-slate-500', lang !== 'en');
        localStorage.setItem('mh_lang', lang);
    }

    // Restore saved language
    const saved = localStorage.getItem('mh_lang');
    if (saved === 'en') setLang('en');

    // ── FAQ accordion ──
    function toggleFaq(btn) {
        const item = btn.closest('.faq-item');
        const body = item.querySelector('.faq-body');
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(el => {
            el.classList.remove('open');
            el.querySelector('.faq-body').classList.remove('open');
        });
        if (!isOpen) {
            item.classList.add('open');
            body.classList.add('open');
        }
    }

    // ── Scroll reveal ──
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>
