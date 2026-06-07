<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PlatformUpload;

class VideoStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $platformUpload;

    /**
     * Create a new notification instance.
     */
    public function __construct(PlatformUpload $platformUpload)
    {
        $this->platformUpload = $platformUpload;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->platformUpload->status);
        $platform = ucfirst($this->platformUpload->platform);
        $title = $this->platformUpload->uploadJob->title;

        $message = (new MailMessage)
                    ->subject("Status Upload [{$status}] - {$platform}")
                    ->greeting("Halo {$notifiable->name},")
                    ->line("Proses upload video Anda ke {$platform} telah mencapai status akhir.")
                    ->line("Video: **{$title}**")
                    ->line("Status: **{$status}**");

        if ($this->platformUpload->status === 'failed') {
            $message->line("Error Detail: " . $this->platformUpload->error_message);
            $message->action('Coba Upload Ulang', url('/history'));
            $message->line("Pastikan Anda sudah mengonfigurasi koneksi dengan benar.");
        } else {
            $message->line("Video Anda berhasil dipublikasikan di {$platform}!");
            $message->action('Lihat Dashboard', url('/dashboard'));
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'platform_upload_id' => $this->platformUpload->id,
            'platform' => $this->platformUpload->platform,
            'status' => $this->platformUpload->status,
            'title' => $this->platformUpload->uploadJob->title,
            'error_message' => $this->platformUpload->error_message,
        ];
    }
}
