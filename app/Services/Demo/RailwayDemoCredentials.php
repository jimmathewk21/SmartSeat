<?php

namespace App\Services\Demo;

use Illuminate\Contracts\Hashing\Hasher;
use RuntimeException;
use Throwable;

/** Owns only the exclusively created reference for this installation attempt. */
class RailwayDemoCredentials
{
    private $file = null;

    private ?array $identity = null;

    private ?string $path = null;

    public function __construct(private Hasher $hasher, private ?string $directory = null) {}

    /** SQL commit must complete inside the callback before the reference is retained. */
    public function withCleanup(callable $operation): array
    {
        try {
            $result = $operation();
            $this->retain();

            return $result;
        } catch (Throwable $exception) {
            $this->discard();
            throw $exception;
        }
    }

    public function create(array $plan): array
    {
        if ($this->path !== null) {
            throw new RuntimeException('Credential reference lifecycle has already started.');
        }
        $directory = $this->directory ?? storage_path('app/private/smartseat-demo/S27-v1');
        $path = $directory.'/credentials.json';
        if (is_link($path) || file_exists($path)) {
            throw new RuntimeException('A private credential reference already exists; it will not be overwritten. Manual review is required.');
        }
        try {
            if (! is_dir($directory) && ! @mkdir($directory, 0700, true)) {
                throw new RuntimeException('Cannot create private credential directory.');
            }
            // Restrict creation itself, rather than relying only on a later chmod.
            $mask = umask(0077);
            try {
                $this->file = @fopen($path, 'x+b');
            } finally {
                umask($mask);
            }
            if ($this->file === false) {
                $this->file = null;
                throw new RuntimeException('Cannot exclusively create private credential reference.');
            }
            $this->path = $path;
            $this->identity = fstat($this->file) ?: null;
            if ($this->identity === null || ! flock($this->file, LOCK_EX | LOCK_NB) || ! @chmod($path, 0600)) {
                throw new RuntimeException('Cannot secure private credential reference.');
            }
            $accounts = [];
            $hashes = [];
            foreach ($plan['tables']['users'] as $email => $user) {
                $password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
                $hashes[$email] = $this->hasher->make($password);
                $owner = str_starts_with($email, 's27.owner.');
                $accounts[] = [
                    'name' => $user['name'], 'email' => $email, 'password' => $password,
                    'login_path' => $owner ? '/internal/rail-owner/entry' : '/trains/sign-in',
                    'owner_approval' => $owner ? 'pending - administrator review required' : null,
                ];
            }
            $payload = json_encode([
                'notice' => 'DEVELOPMENT-ONLY SYNTHETIC ACCOUNTS. Never publish this file.',
                'database' => RailwayDemoPolicy::DATABASE, 'fingerprint' => RailwayDemoPlan::fingerprint($plan), 'accounts' => $accounts,
            ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($this->write($payload) !== strlen($payload) || ! $this->flush()) {
                throw new RuntimeException('Credential write failed.');
            }

            return [$hashes, $path];
        } catch (Throwable) {
            $this->discard();
            // Do not propagate hashing/filesystem exceptions which could contain sensitive data.
            throw new RuntimeException('Private credential creation failed; installation must roll back.');
        }
    }

    /** Call only after a successful SQL commit (including no-op reruns). */
    public function retain(): void
    {
        if (is_resource($this->file)) {
            fclose($this->file);
            $this->file = null;
        }
    }

    public function discard(): void
    {
        if (! is_resource($this->file)) {
            return; // Never touch a reference created by another attempt.
        }
        // Clear the open file we own even if somebody has moved/replaced its pathname.
        $cleared = $this->truncate() && $this->flush();
        fclose($this->file);
        $this->file = null;
        clearstatcache(true, $this->path);
        $current = @lstat($this->path);
        $sameFile = $this->identity !== null && $current !== false && ! is_link($this->path)
            && $current['dev'] === $this->identity['dev'] && $current['ino'] === $this->identity['ino'];
        $removed = $sameFile && $this->remove($this->path);
        if (! $cleared || ! $removed) {
            throw new RuntimeException('Private credential cleanup failed. Installation failed; restrict access and manually review the private reference before retrying. No credentials will be printed.');
        }
    }

    protected function write(string $payload): int|false
    {
        return @fwrite($this->file, $payload);
    }

    protected function flush(): bool
    {
        return @fflush($this->file);
    }

    protected function truncate(): bool
    {
        return @ftruncate($this->file, 0);
    }

    protected function remove(string $path): bool
    {
        return @unlink($path);
    }
}
