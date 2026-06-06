<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PlatformUpload;

class UploadStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $platformUpload;

    public function __construct(PlatformUpload $platformUpload)
    {
        $this->platformUpload = $platformUpload;
    }

    public function envelope(): Envelope
    {
        $statusStr = strtoupper($this->platformUpload->status);
        return new Envelope(
            subject: "[VidFlow] Upload {$statusStr} - {$this->platformUpload->platform}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.upload-status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
