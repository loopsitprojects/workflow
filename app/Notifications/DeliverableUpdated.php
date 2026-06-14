<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliverableUpdated extends Notification
{
    public $deliverable;
    public $message;
    public $type;
    public $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct($deliverable, $message, $type, $actor)
    {
        $this->deliverable = $deliverable;
        $this->message = $message;
        $this->type = $type;
        $this->actor = $actor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('projects.show', $this->deliverable->project_id)
            . '?deliverable_id=' . $this->deliverable->id;

        $plainMessage = strip_tags(
            str_replace(['**', '**'], '', $this->message)
        );

        $project = $this->deliverable->project;
        $brand = $project?->brand;

        $mail = (new MailMessage)
            ->subject('[Loops Work] ' . $this->deliverable->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->actor->name . ' ' . $plainMessage . '.');

        if ($brand) {
            $mail->line('**Brand:** ' . $brand->name);
        }
        if ($project) {
            $mail->line('**Project:** ' . $project->name);
        }

        return $mail
            ->line('**Deliverable:** ' . $this->deliverable->title)
            ->action('View Deliverable', $url)
            ->salutation('— Loops Work');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'deliverable_id' => $this->deliverable->id,
            'deliverable_title' => $this->deliverable->title,
            'message' => $this->message,
            'type' => $this->type,
            'actor_name' => $this->actor->name,
            'actor_avatar' => $this->actor->avatar_url,
            'url' => route('projects.show', $this->deliverable->project_id) . '?deliverable_id=' . $this->deliverable->id,
        ];
    }
}
