<?php

use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\PaymentGateway;
use App\Models\Payment;
use Livewire\Volt\Component;

new class extends Component {

    public function rendering($view): void
    {
        $view->layout('layouts.app');
    }

    public $plans;
    public $activeSubscription;
    public $gateways;
    public $payments;

    public ?int $selectedPlanId    = null;
    public ?int $selectedGatewayId = null;
    public bool $showUpgradeModal  = false;
    public bool $showCancelConfirm = false;

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $this->plans = SubscriptionPlan::active()->get();

        $this->activeSubscription = Subscription::where('user_id', auth()->id())
            ->whereIn('status', ['active', 'trial'])
            ->with('plan', 'gateway')
            ->latest()
            ->first();

        $this->gateways = PaymentGateway::enabled()->get();

        $this->payments = Payment::where('user_id', auth()->id())
            ->with('subscription.plan')
            ->latest()
            ->take(10)
            ->get();
    }

    public function selectPlan(int $id): void
    {
        $this->selectedPlanId = $id;
        $plan = SubscriptionPlan::find($id);
        if ($plan && $plan->isFree()) {
            // For free plans, subscribe directly without showing gateway modal
            $this->subscribe();
        } else {
            $this->selectedGatewayId = $this->gateways->first()?->id;
            $this->showUpgradeModal = true;
        }
    }

    public function selectGateway(int $id): void
    {
        $this->selectedGatewayId = $id;
    }

    public function subscribe(): void
    {
        if (!$this->selectedPlanId) {
            return;
        }

        $plan = SubscriptionPlan::find($this->selectedPlanId);
        if (!$plan) {
            return;
        }

        // Cancel existing active subscription
        Subscription::where('user_id', auth()->id())
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        if ($plan->isFree()) {
            // Create free subscription immediately
            Subscription::create([
                'user_id'    => auth()->id(),
                'plan_id'    => $plan->id,
                'status'     => 'active',
                'starts_at'  => now(),
            ]);

            $this->showUpgradeModal = false;
            $this->selectedPlanId  = null;
            session()->flash('success', app()->getLocale() === 'fr'
                ? 'Vous êtes maintenant sur le plan gratuit.'
                : 'You are now on the free plan.');
        } else {
            // For paid plans: stub — show instructions to configure payment gateway
            $gateway = $this->selectedGatewayId ? PaymentGateway::find($this->selectedGatewayId) : null;

            if (!$gateway) {
                session()->flash('error', app()->getLocale() === 'fr'
                    ? 'Veuillez sélectionner une passerelle de paiement.'
                    : 'Please select a payment gateway.');
                return;
            }

            // Create pending subscription
            Subscription::create([
                'user_id'             => auth()->id(),
                'plan_id'             => $plan->id,
                'payment_gateway_id'  => $gateway->id,
                'status'              => 'pending',
                'starts_at'           => now(),
            ]);

            $this->showUpgradeModal = false;
            $this->selectedPlanId   = null;
            session()->flash('pending', app()->getLocale() === 'fr'
                ? 'Votre abonnement est en attente de paiement. L\'intégration de paiement sera disponible prochainement.'
                : 'Your subscription is pending payment. Payment integration will be available soon.');
        }

        $this->loadData();
    }

    public function confirmCancel(): void
    {
        $this->showCancelConfirm = true;
    }

    public function cancelSubscription(): void
    {
        if ($this->activeSubscription) {
            $this->activeSubscription->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        $this->showCancelConfirm = false;
        session()->flash('success', app()->getLocale() === 'fr'
            ? 'Abonnement annulé.'
            : 'Subscription cancelled.');
        $this->loadData();
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 space-y-6">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('pending'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ session('pending') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
        @endif

        {{-- Current plan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800">
                    {{ app()->getLocale() === 'fr' ? 'Plan actuel' : 'Current Plan' }}
                </h2>
            </div>
            <div class="p-6">
                @if($activeSubscription && $activeSubscription->plan)
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-2xl font-black text-slate-800">{{ $activeSubscription->plan->name }}</h3>
                            @php
                                $statusColor = match($activeSubscription->status) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'trial'  => 'bg-blue-100 text-blue-700',
                                    default  => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ ucfirst($activeSubscription->status) }}
                            </span>
                        </div>
                        @if($activeSubscription->plan->description)
                        <p class="text-sm text-slate-500 mb-3">{{ $activeSubscription->plan->description }}</p>
                        @endif
                        <p class="text-2xl font-black text-indigo-600 mb-4">
                            {{ $activeSubscription->plan->formattedPrice() }}
                        </p>

                        @if(!empty($activeSubscription->plan->features))
                        <ul class="space-y-1.5">
                            @foreach($activeSubscription->plan->features as $feature)
                            <li class="flex items-center gap-2 text-sm text-slate-700">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    <div class="text-right space-y-2">
                        @if($activeSubscription->ends_at)
                        <p class="text-xs text-slate-500">
                            {{ app()->getLocale() === 'fr' ? 'Expire le' : 'Expires' }}
                            <span class="font-semibold text-slate-700">{{ $activeSubscription->ends_at->format('d/m/Y') }}</span>
                        </p>
                        @endif
                        @if($activeSubscription->starts_at)
                        <p class="text-xs text-slate-400">
                            {{ app()->getLocale() === 'fr' ? 'Depuis le' : 'Since' }}
                            {{ $activeSubscription->starts_at->format('d/m/Y') }}
                        </p>
                        @endif
                        @if($activeSubscription->gateway)
                        <p class="text-xs text-slate-400">
                            {{ $activeSubscription->gateway->name }}
                        </p>
                        @endif
                        <button wire:click="confirmCancel"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-xl transition-colors border border-red-200">
                            {{ app()->getLocale() === 'fr' ? 'Annuler l\'abonnement' : 'Cancel Subscription' }}
                        </button>
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 font-semibold">
                        {{ app()->getLocale() === 'fr' ? 'Aucun abonnement actif.' : 'No active subscription.' }}
                    </p>
                    <p class="text-sm text-slate-400 mt-1">
                        {{ app()->getLocale() === 'fr' ? 'Choisissez un plan ci-dessous.' : 'Choose a plan below.' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- Plans comparison --}}
        <div>
            <h2 class="font-bold text-slate-800 text-lg mb-4">
                {{ app()->getLocale() === 'fr' ? 'Changer de plan' : 'Change Plan' }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @forelse($plans as $plan)
                @php
                    $isCurrentPlan = $activeSubscription && $activeSubscription->plan_id === $plan->id;
                @endphp
                <div class="relative bg-white rounded-2xl border {{ $plan->is_featured ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-slate-200' }} shadow-sm overflow-hidden flex flex-col">
                    @if($plan->is_featured)
                    <div class="bg-indigo-600 text-white text-xs font-bold text-center py-1.5 px-4">
                        {{ app()->getLocale() === 'fr' ? 'Recommandé' : 'Recommended' }}
                    </div>
                    @endif

                    <div class="p-6 flex-1">
                        <h3 class="text-lg font-black text-slate-800 mb-1">{{ $plan->name }}</h3>
                        @if($plan->description)
                        <p class="text-xs text-slate-500 mb-4">{{ $plan->description }}</p>
                        @endif

                        <div class="mb-5">
                            @if($plan->isFree())
                                <span class="text-3xl font-black text-slate-800">
                                    {{ app()->getLocale() === 'fr' ? 'Gratuit' : 'Free' }}
                                </span>
                            @else
                                <span class="text-3xl font-black text-slate-800">${{ number_format($plan->price, 2) }}</span>
                                <span class="text-sm text-slate-400 ml-1">/ {{ ucfirst($plan->billing_cycle) }}</span>
                            @endif
                        </div>

                        @if(!empty($plan->features))
                        <ul class="space-y-2 mb-6">
                            @foreach($plan->features as $feature)
                            <li class="flex items-start gap-2 text-sm text-slate-700">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        @if(!empty($plan->limits))
                        <div class="space-y-2 mb-4">
                            @foreach($plan->limits as $key => $val)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="font-bold text-slate-700">{{ is_numeric($val) ? number_format($val) : $val }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="px-6 pb-6">
                        @if($isCurrentPlan)
                        <div class="w-full py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl text-center">
                            {{ app()->getLocale() === 'fr' ? 'Plan actuel' : 'Current Plan' }}
                        </div>
                        @else
                        <button wire:click="selectPlan({{ $plan->id }})"
                                class="w-full py-2.5 text-sm font-semibold rounded-xl transition-colors
                                       {{ $plan->is_featured
                                           ? 'bg-indigo-600 hover:bg-indigo-700 text-white'
                                           : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $plan->isFree()
                                ? (app()->getLocale() === 'fr' ? 'Passer au plan gratuit' : 'Switch to Free')
                                : (app()->getLocale() === 'fr' ? 'S\'abonner' : 'Subscribe') }}
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-12 text-slate-400 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Aucun plan disponible.' : 'No plans available.' }}
                </div>
                @endforelse
            </div>
        </div>

        {{-- Payment history --}}
        @if($payments->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-sm">
                    {{ app()->getLocale() === 'fr' ? 'Historique des paiements' : 'Payment History' }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Date' : 'Date' }}
                            </th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Plan' : 'Plan' }}
                            </th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Montant' : 'Amount' }}
                            </th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ app()->getLocale() === 'fr' ? 'Statut' : 'Status' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($payments as $payment)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 text-slate-700">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3.5 text-slate-700">
                                {{ $payment->subscription?->plan?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">
                                ${{ number_format($payment->amount, 2) }} <span class="text-xs text-slate-400">{{ $payment->currency }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                @php
                                    $statusColor = match($payment->status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'pending'   => 'bg-amber-100 text-amber-700',
                                        'failed'    => 'bg-red-100 text-red-700',
                                        'refunded'  => 'bg-slate-100 text-slate-600',
                                        default     => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Upgrade modal (gateway selection for paid plans) --}}
    @if($showUpgradeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showUpgradeModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-bold text-slate-800 text-lg mb-1">
                {{ app()->getLocale() === 'fr' ? 'Choisir une passerelle de paiement' : 'Choose Payment Gateway' }}
            </h3>
            @php $selectedPlan = $plans->find($selectedPlanId); @endphp
            @if($selectedPlan)
            <p class="text-sm text-slate-500 mb-5">
                {{ app()->getLocale() === 'fr' ? 'Plan sélectionné :' : 'Selected plan:' }}
                <strong class="text-slate-800">{{ $selectedPlan->name }}</strong>
                — ${{ number_format($selectedPlan->price, 2) }}/{{ ucfirst($selectedPlan->billing_cycle) }}
            </p>
            @endif

            @if($gateways->isEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-amber-800 font-semibold">
                    {{ app()->getLocale() === 'fr'
                        ? 'Aucune passerelle de paiement configurée. Contactez l\'administrateur.'
                        : 'No payment gateways configured. Please contact the administrator.' }}
                </p>
            </div>
            @else
            <div class="space-y-3 mb-5">
                @foreach($gateways as $gateway)
                <button wire:click="selectGateway({{ $gateway->id }})"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition-all
                               {{ $selectedGatewayId === $gateway->id
                                   ? 'border-indigo-400 bg-indigo-50 ring-2 ring-indigo-200'
                                   : 'border-slate-200 hover:border-indigo-200 hover:bg-slate-50' }}">
                    @if($gateway->icon)
                    <img src="{{ $gateway->icon }}" alt="{{ $gateway->name }}" class="w-8 h-8 rounded-lg object-contain shrink-0">
                    @else
                    <div class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-xs font-black text-slate-500 shrink-0">
                        {{ strtoupper(substr($gateway->name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-800">{{ $gateway->name }}</p>
                        @if($gateway->is_test_mode)
                        <p class="text-xs text-amber-600">{{ app()->getLocale() === 'fr' ? 'Mode test' : 'Test mode' }}</p>
                        @endif
                    </div>
                    @if($selectedGatewayId === $gateway->id)
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    @endif
                </button>
                @endforeach
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5">
                <p class="text-xs text-amber-800">
                    <strong>{{ app()->getLocale() === 'fr' ? 'Note :' : 'Note:' }}</strong>
                    {{ app()->getLocale() === 'fr'
                        ? ' L\'intégration de paiement complète sera disponible prochainement. Pour l\'instant, un abonnement en attente sera créé.'
                        : ' Full payment integration is coming soon. For now, a pending subscription will be created.' }}
                </p>
            </div>
            @endif

            <div class="flex gap-3">
                @if($gateways->isNotEmpty())
                <button wire:click="subscribe"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Confirmer' : 'Confirm' }}
                </button>
                @endif
                <button wire:click="$set('showUpgradeModal', false)"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel confirmation modal --}}
    @if($showCancelConfirm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" wire:click="$set('showCancelConfirm', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">
                        {{ app()->getLocale() === 'fr' ? 'Annuler l\'abonnement ?' : 'Cancel Subscription?' }}
                    </h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ app()->getLocale() === 'fr'
                            ? 'Vous perdrez l\'accès aux fonctionnalités premium.'
                            : 'You will lose access to premium features.' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="cancelSubscription"
                        class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Confirmer l\'annulation' : 'Confirm Cancellation' }}
                </button>
                <button wire:click="$set('showCancelConfirm', false)"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    {{ app()->getLocale() === 'fr' ? 'Garder l\'abonnement' : 'Keep Subscription' }}
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
