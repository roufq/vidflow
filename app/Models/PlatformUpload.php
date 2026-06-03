<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformUpload extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_id',
        'platform',
        'connection_id',
        'status',
        'progress_percent',
        'platform_video_id',
        'platform_url',
        'error_message',
        'retry_count',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function uploadJob()
    {
        return $this->belongsTo(UploadJob::class, 'job_id');
    }
}
