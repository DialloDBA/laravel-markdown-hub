<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function rendering($view): void
    {
        $view->layout('layouts.admin');
    }


    public string $search    = '';
    public ?int $viewingId   = null;
    public $viewingUser      = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function changeRole(int $id, string $role): void
    {
        if (!in_array($role, ['user', 'admin'])) {
            return;
        }
        User::findOrFail($id)->update(['role' => $role]);
        session()->flash('success', app()->getLocale() === 'fr'
            ? 'Rôle mis à jour.'
            : 'Role updated.');
    }

    public function viewUser(int $id): void
    {
        $this->viewingId = $id;
        $this->viewingUser = User::withCount(['readmeFiles', 'folders'])
            ->with(['activeSubscription.plan'])
            ->findOrFail($id);
    }

    public function closeView(): void
    {
        $this->viewingId = null;
        $this->viewingUser = null;
    }

    public function with(): array
    {
        $users = User::withCount(['readmeFiles'])
            ->with(['activeSubscription.plan'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return ['users' => $users];
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

        {{-- Header & Search --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">
                    {{ app()->getLocale() === 'fr' ? 'Utilisateurs' : 'Users' }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ app()->getLocale() === 'fr' ? 'Gérez les comptes utilisateurs.' : 'Manage user accounts.' }}
                </p>
            </div>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="{{ app()->getLocale() === 'fr' ? 'Rechercher par nom ou email...' : 'Search by name or email...' }}"
                       class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-full sm:w-72">
            </div>
        </div>

        {{-- Users Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Utilisateur' : 'User' }}
                            </th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Rôle' : 'Role' }}
                            </th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Abonnement' : 'Subscription' }}
                            </th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Fichiers' : 'Files' }}
                            </th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Inscrit le' : 'Registered' }}
                            </th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-colors
                                                   {{ $user->role === 'admin' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                        {{ ucfirst($user->role) }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false"
                                         class="absolute left-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-10 overflow-hidden">
                                        <button wire:click="changeRole({{ $user->id }}, 'user')" @click="open = false"
                                                class="block w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                                            User
                                        </button>
                                        <button wire:click="changeRole({{ $user->id }}, 'admin')" @click="open = false"
                                                class="block w-full text-left px-4 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-50 transition-colors">
                                            Admin
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->activeSubscription && $user->activeSubscription->plan)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            {{ $user->activeSubscription->plan->name }}
                                        </span>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($user->activeSubscription->status) }}</p>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">
                                        {{ app()->getLocale() === 'fr' ? 'Aucun' : 'None' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    {{ $user->readme_files_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $user->created_at->format('d/m/Y') }}
                                <span class="block text-slate-400">{{ $user->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="viewUser({{ $user->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                            title="{{ app()->getLocale() === 'fr' ? 'Voir les détails' : 'View details' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                                {{ app()->getLocale() === 'fr' ? 'Aucun utilisateur trouvé.' : 'No users found.' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- User Detail Modal --}}
    @if($viewingUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="closeView"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-800 text-lg">
                    {{ app()->getLocale() === 'fr' ? 'Détails de l\'utilisateur' : 'User Details' }}
                </h3>
                <button wire:click="closeView" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- User info --}}
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-400 to-blue-500 flex items-center justify-center text-white text-xl font-black shrink-0">
                    {{ strtoupper(substr($viewingUser->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-lg">{{ $viewingUser->name }}</h4>
                    <p class="text-sm text-slate-500">{{ $viewingUser->email }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $viewingUser->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($viewingUser->role) }}
                        </span>
                        <span class="text-xs text-slate-400">
                            {{ app()->getLocale() === 'fr' ? 'Inscrit' : 'Joined' }} {{ $viewingUser->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-slate-800">{{ $viewingUser->readme_files_count ?? 0 }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ app()->getLocale() === 'fr' ? 'Fichiers README' : 'README Files' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-black text-slate-800">{{ $viewingUser->folders_count ?? 0 }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ app()->getLocale() === 'fr' ? 'Dossiers' : 'Folders' }}</p>
                </div>
            </div>

            {{-- Subscription --}}
            <div class="mb-4">
                <h5 class="text-sm font-bold text-slate-700 mb-2">
                    {{ app()->getLocale() === 'fr' ? 'Abonnement actif' : 'Active Subscription' }}
                </h5>
                @if($viewingUser->activeSubscription && $viewingUser->activeSubscription->plan)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-emerald-800">{{ $viewingUser->activeSubscription->plan->name }}</p>
                                <p class="text-xs text-emerald-600 mt-0.5">{{ ucfirst($viewingUser->activeSubscription->status) }}</p>
                            </div>
                            @if($viewingUser->activeSubscription->ends_at)
                            <div class="text-right">
                                <p class="text-xs text-emerald-600">
                                    {{ app()->getLocale() === 'fr' ? 'Expire le' : 'Expires' }}
                                </p>
                                <p class="text-sm font-semibold text-emerald-800">
                                    {{ $viewingUser->activeSubscription->ends_at->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-sm text-slate-500">
                            {{ app()->getLocale() === 'fr' ? 'Aucun abonnement actif.' : 'No active subscription.' }}
                        </p>
                    </div>
                @endif
            </div>

            <button wire:click="closeView"
                    class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                {{ app()->getLocale() === 'fr' ? 'Fermer' : 'Close' }}
            </button>
        </div>
    </div>
    @endif

</div>
