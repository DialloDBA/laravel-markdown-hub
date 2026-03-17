<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model {
    protected $fillable = ['name','slug','icon','config','is_enabled','is_default','is_test_mode','description'];
    protected $casts = ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'is_test_mode' => 'boolean'];
    protected $hidden = ['config'];

    protected function config(): Attribute {
        return Attribute::make(
            get: fn($v) => $v ? json_decode(Crypt::decryptString($v), true) : [],
            set: fn($v) => $v ? Crypt::encryptString(json_encode($v)) : null,
        );
    }

    public function scopeEnabled($q) { return $q->where('is_enabled', true); }
    public static function getDefault(): ?self { return self::where('is_default', true)->where('is_enabled', true)->first(); }
}
