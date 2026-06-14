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
        $brand = $this->project->brand;

        $subject = $this->isUpdate
            ? '[Loops Work] Brief uploaded: ' . $this->project->name
            : '[Loops Work] New project: ' . $this->project->name;
        $line = $this->isUpdate
            ? $this->actor->name . ' uploaded a brief for **' . $this->project->name . '**.'
            : $this->actor->name . ' created a new project and added you to the team.';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($line);

        if ($brand) {
            $mail->line('**Brand:** ' . $brand->name);
        }

        $mail->line('**Project:** ' . $this->project->name);

        if ($this->isUpdate) {
            $mail->line('A brief has been uploaded — review it before starting work.');
        }

        return $mail
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
