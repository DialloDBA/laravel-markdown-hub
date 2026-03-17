<x-admin-layout>
    <x-slot name="title">
        {{ app()->getLocale() === 'fr' ? 'Vue d\'ensemble' : 'Overview' }}
    </x-slot>

    @php
        $totalUsers         = \App\Models\User::count();
        $activeSubscriptions = \App\Models\Subscription::whereIn('status', ['active', 'trial'])->count();
        $aiProvidersCount   = \App\Models\AiProvider::where('is_enabled', true)->count();
        $monthlyRevenue     = \App\Models\Payment::where('status', 'completed')
                                ->whereMonth('paid_at', now()->month)
                                ->whereYear('paid_at', now()->year)
                                ->sum('amount');
        $recentUsers        = \App\Models\User::latest()->take(5)->get();
        $recentPayments     = \App\Models\Payment::with(['user'])->latest()->take(5)->get();
    @endphp

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        {{-- Total Users --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Utilisateurs' : 'Total Users' }}
                </p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalUsers) }}</p>
            </div>
        </div>

        {{-- Active Subscriptions --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Abonnements actifs' : 'Active Subscriptions' }}
                </p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($activeSubscriptions) }}</p>
            </div>
        </div>

        {{-- AI Providers --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Fournisseurs IA actifs' : 'Active AI Providers' }}
                </p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($aiProvidersCount) }}</p>
            </div>
        </div>

        {{-- Monthly Revenue --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    {{ app()->getLocale() === 'fr' ? 'Revenus ce mois' : 'Monthly Revenue' }}
                </p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">${{ number_format($monthlyRevenue, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Two column tables --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Recent Users --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Derniers utilisateurs inscrits' : 'Recent Registrations' }}
                </h2>
                <a href="{{ route('admin.users') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">
                    {{ app()->getLocale() === 'fr' ? 'Voir tous →' : 'View all →' }}
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentUsers as $user)
                <div class="px-6 py-3.5 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Admin</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">User</span>
                        @endif
                        <span class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">
                    {{ app()->getLocale() === 'fr' ? 'Aucun utilisateur.' : 'No users yet.' }}
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Derniers paiements' : 'Recent Payments' }}
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentPayments as $payment)
                <div class="px-6 py-3.5 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $payment->user?->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-sm font-bold text-slate-800">${{ number_format($payment->amount, 2) }}</span>
                        @php
                            $statusColor = match($payment->status) {
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'failed'    => 'bg-red-100 text-red-700',
                                'refunded'  => 'bg-slate-100 text-slate-600',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">
                    {{ app()->getLocale() === 'fr' ? 'Aucun paiement.' : 'No payments yet.' }}
                </div>
                @endforelse
            </div>
        </div>

    </div>
</x-admin-layout>
