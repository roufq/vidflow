<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformConnection extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'platform',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'platform_user_id',
        'platform_username',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
