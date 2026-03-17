<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features',
        'limits',
        'is_active',
        'is_featured',
        'sort_order'
    ];
    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function isFree(): bool
    {
        return $this->price == 0 || $this->billing_cycle === 'free';
    }

    public function formattedPrice(): string
    {
        if ($this->isFree()) return __('dashboard.free');
        return '$' . number_format($this->price, 2) . '/' . __('dashboard.billing_' . $this->billing_cycle);
    }
}
