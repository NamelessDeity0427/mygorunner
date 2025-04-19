<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BookingNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $channels;
    protected $title;

    public function __construct(string $message, array $channels, ?string $title = null)
    {
        $this->message = $message;
        $this->channels = $channels;
        $this->title = $title;
    }

    public function via($notifiable): array
    {
        return $this->channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title ?? 'MyGoRunner Notification')
            ->line($this->message)
            ->action('View Details', url('/dashboard'));
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'message' => $this->message,
            'title' => $this->title ?? 'Notification',
            'type' => 'booking_update',
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'title' => $this->title,
        ];
    }
}