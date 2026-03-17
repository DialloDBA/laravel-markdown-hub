<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ── Changement de langue / Language switch ──────────────────────────────────
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['fr', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// ── Page d'accueil / Welcome ─────────────────────────────────────────────────
Route::view('/', 'welcome');

// ── Routes authentifiées (utilisateurs) ─────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('import', 'import')->name('import');
    Route::post('import', [\App\Http\Controllers\ImportController::class, 'store'])->name('import.store');

    Route::view('profile', 'profile')->name('profile');

    Volt::route('ai-assistant', 'ai-assistant')->name('ai-assistant');

    Volt::route('subscription', 'subscription')->name('subscription');

    // Export PDF d'un fichier README
    Route::get('/readme/{file}/pdf', function (\App\Models\ReadmeFile $file) {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>
                    body {
                        font-family: sans-serif;
                        line-height: 1.6;
                        color: #333;
                        margin: 20px;
                    }
                    h1, h2, h3 { border-bottom: 1px solid #eee; padding-bottom: 5px; }
                    pre {
                        background: #f6f8fa;
                        padding: 16px;
                        border-radius: 6px;
                        white-space: pre-wrap;
                        white-space: -moz-pre-wrap;
                        white-space: -pre-wrap;
                        white-space: -o-pre-wrap;
                        word-wrap: break-word;
                        font-size: 12px;
                        border: 1px solid #ddd;
                    }
                    code {
                        font-family: monospace;
                        background: #f0f0f0;
                        padding: 2px 4px;
                        border-radius: 3px;
                        font-size: 90%;
                        word-break: break-all;
                    }
                    pre code {
                        background: transparent;
                        padding: 0;
                    }
                    img { max-width: 100%; height: auto; display: block; margin: 10px 0; }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                        table-layout: fixed;
                    }
                    table, th, td { border: 1px solid #dfe2e5; }
                    th, td {
                        padding: 8px 12px;
                        text-align: left;
                        word-wrap: break-word;
                        font-size: 13px;
                    }
                    th { background-color: #f6f8fa; }
                    blockquote {
                        margin: 0;
                        padding: 0 1em;
                        color: #6a737d;
                        border-left: 0.25em solid #dfe2e5;
                    }
                </style>
            </head>
            <body>
                <div style="margin-bottom: 30px; text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px;">
                    <h1 style="margin: 0;">' . htmlspecialchars($file->name) . '</h1>
                    <p style="color: #666; font-size: 12px;">Généré par MarkdownHub le ' . now()->format('d/m/Y H:i') . '</p>
                </div>
                ' . $file->rendered_content . '
            </body>
            </html>
        ');
        return $pdf->download($file->name . '.pdf');
    })->name('readme.pdf');
});

// ── Routes Admin ─────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', \App\Http\Middleware\IsAdmin::class])
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Volt::route('ai-providers', 'admin.ai-providers')->name('ai-providers');
        Volt::route('payment-gateways', 'admin.payment-gateways')->name('payment-gateways');
        Volt::route('subscription-plans', 'admin.subscription-plans')->name('subscription-plans');
        Volt::route('users', 'admin.users')->name('users');
        Volt::route('settings', 'admin.settings')->name('settings');
    });

require __DIR__.'/auth.php';
