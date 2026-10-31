<?php

namespace App\Support;

final class FormValue
{
    /** Invalid structured input must not break the page showing its validation error. */
    public static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
