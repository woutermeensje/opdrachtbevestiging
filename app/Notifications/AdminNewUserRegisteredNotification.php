<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewUserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $user,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nieuwe aanmelding op '.config('app.name'))
            ->greeting('Nieuwe gebruiker geregistreerd')
            ->line('Er is zojuist een nieuwe gebruiker aangemeld op '.config('app.name').'.')
            ->line('Naam: '.$this->user->name)
            ->line('E-mailadres: '.$this->user->email)
            ->line('Bedrijf: '.$this->user->company_name)
            ->line('KvK-nummer: '.$this->user->kvk_number);
    }
}
