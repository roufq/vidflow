<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'plan'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles;

    protected $appends = ['upload_limit', 'max_scheduling_days', 'max_file_size_mb'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getUploadLimitAttribute(): int
    {
        return match($this->plan) {
            'free' => 5,
            'standard' => 25,
            'pro' => 80,
            'business' => -1, // -1 for unlimited
            default => 5,
        };
    }

    public function getMaxSchedulingDaysAttribute(): int
    {
        return match($this->plan) {
            'free' => 3,
            'standard' => 30,
            'pro' => 90,
            'business' => 365,
            default => 3,
        };
    }

    public function getMaxFileSizeMbAttribute(): int
    {
        return match($this->plan) {
            'free' => 100, // 100 MB
            'standard' => 250, // 250 MB
            'pro' => 500, // 500 MB
            'business' => 1024, // 1 GB
            default => 100,
        };
    }
}
