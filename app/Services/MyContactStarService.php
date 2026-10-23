<?php

namespace App\Services;

use App\Models\MyContact;

class MyContactStarService
{
    public function calculate(array|MyContact $contact): int
    {
        $stars = 0;

        if ($this->hasPriorityCategory($contact)) {
            $stars++;
        }

        if ($this->hasExperienceAboveTenYears($contact)) {
            $stars++;
        }

        if ($this->isKeyPerson($contact)) {
            $stars++;
        }

        if ($this->hasKeyRole($contact)) {
            $stars++;
        }

        if ($this->isVipSpeakerTag($contact)) {
            $stars++;
        }

        return min(5, $stars);
    }

    private function isVipSpeakerTag(array|MyContact $contact): bool
    {
        return $this->normalizeText($this->value($contact, 'speaker_tag')) === 'vip';
    }

    private function hasKeyRole(array|MyContact $contact): bool
    {
        return $this->normalizeText($this->value($contact, 'role')) !== '';
    }

    private function isKeyPerson(array|MyContact $contact): bool
    {
        return filter_var($this->value($contact, 'is_whitelisted'), FILTER_VALIDATE_BOOL);
    }

    private function hasExperienceAboveTenYears(array|MyContact $contact): bool
    {
        $experience = $this->normalizeText($this->value($contact, 'experience'));

        if ($experience === '') {
            return false;
        }

        if (preg_match('/\b(more than|over|above)\s+10\b/i', $experience)) {
            return true;
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*\+\s*(?:years?|yrs?|yr)\b/i', $experience, $matches)) {
            return (float) $matches[1] > 10;
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*[-\x{2013}]\s*(\d+(?:\.\d+)?)\s*(?:years?|yrs?|yr)\b/iu', $experience, $matches)) {
            return (float) $matches[2] > 10;
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*(?:years?|yrs?|yr)\b/i', $experience, $matches)) {
            return (float) $matches[1] > 10;
        }

        return false;
    }

    private function hasPriorityCategory(array|MyContact $contact): bool
    {
        $category = $this->normalizeCategoryText($this->value($contact, 'category'));

        if ($category === '') {
            return false;
        }

        foreach ([
            'corporate',
            'judge',
            'govt organisation',
            'govt organization',
            'government organisation',
            'government organization',
        ] as $needle) {
            if ($category === $needle || str_contains($category, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeText(mixed $value): string
    {
        return trim(mb_strtolower((string) $value));
    }

    private function normalizeCategoryText(mixed $value): string
    {
        $normalized = mb_strtolower((string) $value);
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $normalized) ?? '');
    }

    private function value(array|MyContact $contact, string $key): mixed
    {
        if (is_array($contact)) {
            return $contact[$key] ?? null;
        }

        return $contact->getAttribute($key);
    }
}
