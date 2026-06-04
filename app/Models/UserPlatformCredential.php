<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlatformCredential extends Model
{
    protected $fillable = ['user_id', 'platform', 'app_id', 'app_secret'];
}
