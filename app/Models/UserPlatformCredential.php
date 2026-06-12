<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class UserPlatformCredential extends Model
{
    protected $fillable = ['user_id', 'platform', 'app_id', 'app_secret'];

    /**
     * Get decrypted app_id.
     */
    public function getAppIdAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Fallback jika data lama disimpan dalam bentuk plain text
            return $value;
        }
    }

    /**
     * Set encrypted app_id.
     */
    public function setAppIdAttribute($value): void
    {
        $this->attributes['app_id'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted app_secret.
     */
    public function getAppSecretAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Fallback jika data lama disimpan dalam bentuk plain text
            return $value;
        }
    }

    /**
     * Set encrypted app_secret.
     */
    public function setAppSecretAttribute($value): void
    {
        $this->attributes['app_secret'] = $value ? Crypt::encryptString($value) : null;
    }
}
