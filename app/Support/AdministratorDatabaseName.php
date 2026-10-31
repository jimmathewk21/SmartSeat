<?php

namespace App\Support;

final class AdministratorDatabaseName
{
    /**
     * Approve the base name or an exact, explicitly permitted suffix.
     * Suffixes are lowercase alphanumeric groups separated by single underscores.
     */
    public static function allows(string $database, array $permittedSuffixes = []): bool
    {
        if ($database === 'smartseat_rebuild') {
            return true;
        }

        foreach ($permittedSuffixes as $suffix) {
            if (is_string($suffix)
                && preg_match('/\A[a-z0-9]+(?:_[a-z0-9]+)*\z/', $suffix) === 1
                && $database === 'smartseat_rebuild_'.$suffix) {
                return true;
            }
        }

        return false;
    }
}
