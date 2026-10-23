<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlainTextMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $subjectLine,
        private readonly string $textBody
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject($this->subjectLine)
            ->text('emails.plain-text')
            ->with([
                'textBody' => $this->textBody,
            ]);
    }
}
