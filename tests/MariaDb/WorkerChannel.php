<?php

namespace Tests\MariaDb;

use RuntimeException;
use Throwable;

/** Bounded JSON-line transport over nonblocking TCP, including on Windows. No Laravel boot. */
final class WorkerChannel
{
    public const MAX_FRAME_BYTES = 65536;

    private string $input = '';

    private string $output = '';

    private array $messages = [];

    public function __construct(private $socket, private float $deadline)
    {
        if (! stream_set_blocking($socket, false)) {
            throw new RuntimeException('Worker socket does not support nonblocking I/O.');
        }
    }

    public static function clock(): float
    {
        return hrtime(true) / 1.0e9;
    }

    public static function connect(array $arguments): self
    {
        if (count($arguments) !== 5
            || preg_match('/\Atcp:\/\/127\.0\.0\.1:[0-9]{1,5}\z/', $arguments[1]) !== 1
            || preg_match('/\A[a-f0-9]{64}\z/', $arguments[2]) !== 1
            || ! in_array($arguments[3], ['0', '1', '2', '3'], true)
            || ! is_numeric($arguments[4]) || (float) $arguments[4] <= 0 || (float) $arguments[4] > 120) {
            throw new RuntimeException('Invalid worker transport arguments.');
        }
        $deadline = self::clock() + (float) $arguments[4];
        $socket = @stream_socket_client($arguments[1], $code, $error, min(5.0, (float) $arguments[4]));
        if (! is_resource($socket)) {
            throw new RuntimeException('Unable to connect to the worker coordinator.');
        }
        $channel = new self($socket, $deadline);
        $channel->send(['phase' => 'hello', 'slot' => (int) $arguments[3], 'token' => $arguments[2], 'pid' => getmypid()]);

        return $channel;
    }

    public function queue(#[\SensitiveParameter] array $message): void
    {
        $json = json_encode($message, JSON_THROW_ON_ERROR)."\n";
        if (strlen($json) > self::MAX_FRAME_BYTES || strlen($this->output) + strlen($json) > self::MAX_FRAME_BYTES) {
            throw new RuntimeException('Worker message exceeds its size limit.');
        }
        $this->output .= $json;
    }

    /** Never waits for a peer; handles partial reads/writes and bounded buffering. */
    public function poll(): array
    {
        if ($this->output !== '') {
            $written = @fwrite($this->socket, $this->output, 32768);
            if ($written === false) {
                throw new RuntimeException('Worker socket write failed.');
            }
            $this->output = substr($this->output, $written);
        }
        // Limit work per poll so a noisy peer cannot starve other workers/deadlines.
        for ($read = 0; $read < 4; $read++) {
            $chunk = @fread($this->socket, 16384);
            if ($chunk === false) {
                throw new RuntimeException('Worker socket read failed.');
            }
            if ($chunk === '') {
                break;
            }
            $this->input .= $chunk;
            if (strlen($this->input) > self::MAX_FRAME_BYTES) {
                throw new RuntimeException('Worker message exceeds its size limit.');
            }
        }
        $messages = [];
        while (($newline = strpos($this->input, "\n")) !== false) {
            $line = substr($this->input, 0, $newline);
            $this->input = substr($this->input, $newline + 1);
            try {
                $message = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                throw new RuntimeException('Malformed worker message.');
            }
            if (! is_array($message) || ! is_string($message['phase'] ?? null)) {
                throw new RuntimeException('Malformed worker message.');
            }
            $messages[] = $message;
        }
        if ($this->closed() && $this->input !== '') {
            throw new RuntimeException('Worker disconnected with an incomplete message.');
        }

        return $messages;
    }

    public function send(#[\SensitiveParameter] array $message): void
    {
        $this->queue($message);
        do {
            $this->messages = array_merge($this->messages, $this->poll());
            $this->checkDeadline();
            if ($this->output !== '') {
                usleep(5000);
            }
        } while ($this->output !== '');
    }

    public function receive(string $phase): array
    {
        while ($this->messages === []) {
            $this->messages = $this->poll();
            $this->checkDeadline();
            if ($this->messages === []) {
                usleep(5000);
            }
        }
        $message = array_shift($this->messages);
        if ($message['phase'] !== $phase) {
            throw new RuntimeException('Worker received an unexpected coordination phase.');
        }

        return $message;
    }

    public function barrier(string $phase, string $release, array $metadata = []): void
    {
        $this->send(['phase' => $phase] + $metadata);
        $this->receive($release);
    }

    public function closed(): bool
    {
        return ! is_resource($this->socket) || feof($this->socket);
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    private function checkDeadline(): void
    {
        if (self::clock() > $this->deadline) {
            throw new RuntimeException('Worker communication timed out.');
        }
        if ($this->closed() && $this->messages === []) {
            throw new RuntimeException('Worker coordinator disconnected.');
        }
    }
}
