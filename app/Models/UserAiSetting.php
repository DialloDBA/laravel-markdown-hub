<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class UserAiSetting extends Model {
    protected $fillable = ['user_id','ai_provider_id','selected_model','custom_api_key'];
    protected $hidden = ['custom_api_key'];

    protected function customApiKey(): Attribute {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    public function user() { return $this->belongsTo(User::class); }
    public function provider() { return $this->belongsTo(AiProvider::class, 'ai_provider_id'); }
}
