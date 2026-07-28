<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GenericAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $payload,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => (string) ($this->payload['title'] ?? 'Pemberitahuan'),
            'message' => (string) ($this->payload['message'] ?? ''),
            'url' => (string) ($this->payload['url'] ?? route('notifications.index')),
            'category' => (string) ($this->payload['category'] ?? 'general'),
            'icon' => (string) ($this->payload['icon'] ?? 'bi-bell'),
            'tag' => (string) ($this->payload['tag'] ?? 'general'),
            'priority' => (string) ($this->payload['priority'] ?? 'normal'),
            'push' => [
                'title' => (string) ($this->payload['push_title'] ?? $this->payload['title'] ?? 'Pemberitahuan'),
                'body' => (string) ($this->payload['push_body'] ?? $this->payload['message'] ?? ''),
                'url' => (string) ($this->payload['url'] ?? route('notifications.index')),
                'tag' => (string) ($this->payload['tag'] ?? 'general'),
                'category' => (string) ($this->payload['category'] ?? 'general'),
            ],
        ];
    }
}
