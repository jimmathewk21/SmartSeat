<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HtmlBodyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $subjectLine,
        private readonly string $htmlBody,
        private readonly array $attachmentDefinitions = []
    ) {
    }

    public function build(): self
    {
        $mail = $this
            ->subject($this->subjectLine)
            ->html($this->htmlBody);

        foreach ($this->attachmentDefinitions as $attachment) {
            $path = (string) ($attachment['path'] ?? '');
            if ($path === '' || ! is_file($path)) {
                continue;
            }

            $options = [];
            $name = trim((string) ($attachment['as'] ?? ''));
            $mime = trim((string) ($attachment['mime'] ?? ''));

            if ($name !== '') {
                $options['as'] = $name;
            }

            if ($mime !== '') {
                $options['mime'] = $mime;
            }

            $mail->attach($path, $options);
        }

        return $mail;
    }
}
