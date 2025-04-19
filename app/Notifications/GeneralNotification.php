<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $channel;
    protected $title;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $channel, ?string $title = null)
    {
        $this->message = $message;
        $this->channel = $channel;
        $this->title = $title;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [$this->channel];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title ?? 'System Notification')
            ->line($this->message)
            ->action('View Details', url('/'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'title' => $this->title,
        ];
    }
}