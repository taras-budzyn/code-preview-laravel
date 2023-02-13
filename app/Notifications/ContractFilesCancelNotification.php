<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PauseContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ContractFilesCancelNotification extends Notification
{
    /** @var PauseContract */
    private $pauseContract;

    public function __construct(PauseContract $pauseContract)
    {
        $this->pauseContract = $pauseContract;
    }

    /**
     * //phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(__("Text"))
            ->greeting('Text,')
            ->line(new HtmlString(
                'Text'
            ))
            ->line($this->pauseContract->reason)
            ->salutation(new HtmlString('Text'));
    }

    public function via(): array
    {
        return ['mail'];
    }
}
