<?php

namespace App\Models;

use App\Models\ReadmeFile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function aiSetting()
    {
        return $this->hasOne(UserAiSetting::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trial'])->latest();
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function readmeFiles()
    {
        return $this->hasMany(ReadmeFile::class);
    }
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
