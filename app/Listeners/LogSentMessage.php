<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Models\Email;
use Illuminate\Mail\Events\MessageSent;

class LogSentMessage
{
    public function handle(MessageSent $event): void
    {
        $email = new Email();
        $email->subject = $event->message->getSubject();
        $email->to = implode(',', array_keys($event->message->getTo()));
        $email->notification = $event->data['__laravel_notification'] ?? null;
        $email->save();
    }
}
