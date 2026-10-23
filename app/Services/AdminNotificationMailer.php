<?php

namespace App\Services;

use App\Mail\HtmlBodyMail;
use App\Models\User;
use App\Support\AdminEmailTemplateSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationMailer
{
    public function send(string $templateKey, array $placeholders = [], array $attachments = [], ?array $recipientOverride = null): void
    {
        $settings = AdminEmailTemplateSettings::get();
        $template = $settings['templates'][$templateKey] ?? null;

        if (! is_array($template) || ! ($template['enabled'] ?? false)) {
            return;
        }

        $subject = $this->replaceSubject((string) ($template['subject'] ?? ''), $placeholders);
        $body = $this->replaceBody((string) ($template['body'] ?? ''), $placeholders);
        $recipients = collect($recipientOverride)
            ->filter(fn ($email) => is_string($email) && trim($email) !== '')
            ->map(fn ($email) => trim($email))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            $recipients = $this->adminRecipients($templateKey);
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->queue(new HtmlBodyMail($subject, $body, $attachments));
            } catch (\Throwable $exception) {
                Log::warning('Failed to send admin notification email.', [
                    'template' => $templateKey,
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function adminRecipients(string $templateKey)
    {
        if (in_array($templateKey, ['admin_login', 'speaker_application'], true)) {
            $primaryEmail = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('title', 'Admin');
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('id')
                ->value('email');

            if (is_string($primaryEmail) && trim($primaryEmail) !== '') {
                return collect([trim($primaryEmail)]);
            }
        }

        $recipients = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('title', 'Admin');
            })
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            $fallback = (string) config('mail.from.address', '');
            if ($fallback !== '') {
                return collect([$fallback]);
            }
        }

        return $recipients;
    }

    private function replaceSubject(string $template, array $placeholders): string
    {
        return trim(strtr($template, $this->subjectReplacements($placeholders)));
    }

    private function replaceBody(string $template, array $placeholders): string
    {
        return strtr($template, $this->bodyReplacements($placeholders));
    }

    private function subjectReplacements(array $placeholders): array
    {
        $replacements = [];
        foreach ($placeholders as $key => $value) {
            $replacements['{{' . $key . '}}'] = $this->stringify($value);
        }

        return $replacements;
    }

    private function bodyReplacements(array $placeholders): array
    {
        $replacements = [];
        foreach ($placeholders as $key => $value) {
            $replacements['{{' . $key . '}}'] = e($this->stringify($value));
        }

        return $replacements;
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item) => $this->stringify($item), $value));
        }

        return trim((string) $value) !== '' ? trim((string) $value) : '-';
    }
}
