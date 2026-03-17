<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {
    protected $fillable = ['user_id','plan_id','payment_gateway_id','status','gateway_subscription_id','trial_ends_at','starts_at','ends_at','cancelled_at'];
    protected $casts = ['trial_ends_at' => 'datetime', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function gateway() { return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id'); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function isActive(): bool { return in_array($this->status, ['active', 'trial']); }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
