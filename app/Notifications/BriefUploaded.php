<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BriefUploaded extends Notification
{
    public function __construct(
        public Project $project,
        public User $actor,
        public bool $isUpdate = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('projects.show', $this->project);
        $subject = $this->isUpdate
            ? '[Loops Work] Brief uploaded: ' . $this->project->name
            : '[Loops Work] New project assigned: ' . $this->project->name;
        $line = $this->isUpdate
            ? $this->actor->name . ' uploaded a brief for project **' . $this->project->name . '**.'
            : $this->actor->name . ' created and assigned you to project **' . $this->project->name . '**.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($line)
            ->action('View Project', $url)
            ->salutation('— Loops Work');
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->isUpdate
            ? 'uploaded a brief for **' . $this->project->name . '**'
            : 'assigned you to project **' . $this->project->name . '**';

        return [
            'deliverable_title' => $this->project->name,
            'message' => $message,
            'type' => 'brief_uploaded',
            'actor_name' => $this->actor->name,
            'actor_avatar' => $this->actor->avatar_url,
            'url' => route('projects.show', $this->project),
        ];
    }
}
