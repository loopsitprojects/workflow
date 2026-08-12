<?php

namespace App\Notifications;

use App\Models\ArtworkReview;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArtworkReviewSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public ArtworkReview $review,
        public string $clientName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deliverable = $this->review->deliverable;
        $project = $deliverable?->project;
        $brand = $project?->brand;

        $url = route('artwork.dashboard', $deliverable);

        $mail = (new MailMessage)
            ->subject('[Loops Work] Client Artwork Review Submitted: ' . ($deliverable->title ?? 'Artwork'))
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('The client **' . $this->clientName . '** has submitted an artwork review on **' . ($deliverable->title ?? 'Artwork') . '**.')
            ->line('**Total Annotations:** ' . $this->review->annotations()->count());

        if ($brand) {
            $mail->line('**Brand:** ' . $brand->name);
        }
        if ($project) {
            $mail->line('**Project:** ' . $project->name);
        }

        return $mail
            ->action('View Client Annotations', $url)
            ->salutation('— Loops Work');
    }

    public function toArray(object $notifiable): array
    {
        $deliverable = $this->review->deliverable;
        return [
            'deliverable_id' => $deliverable?->id,
            'deliverable_title' => $deliverable?->title,
            'message' => 'submitted an artwork review for **' . ($deliverable->title ?? 'Artwork') . '**',
            'type' => 'artwork_review_submitted',
            'actor_name' => $this->clientName,
            'actor_avatar' => null,
            'url' => route('artwork.dashboard', $deliverable),
        ];
    }
}
