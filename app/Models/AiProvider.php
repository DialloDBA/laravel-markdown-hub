<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model {
    protected $fillable = ['name','slug','icon','base_url','api_key','models','is_enabled','sort_order','description'];
    protected $casts = ['models' => 'array', 'is_enabled' => 'boolean'];
    protected $hidden = ['api_key'];

    protected function apiKey(): Attribute {
        return Attribute::make(
            get: fn($v) => $v ? Crypt::decryptString($v) : null,
            set: fn($v) => $v ? Crypt::encryptString($v) : null,
        );
    }

    public function userSettings() { return $this->hasMany(UserAiSetting::class); }
    public function scopeEnabled($q) { return $q->where('is_enabled', true)->orderBy('sort_order'); }
}
