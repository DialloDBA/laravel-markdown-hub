<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = ['user_id','subscription_id','amount','currency','status','gateway_payment_id','invoice_url','metadata','paid_at'];
    protected $casts = ['paid_at' => 'datetime', 'metadata' => 'array', 'amount' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
}
