<?php

declare(strict_types=1);

namespace Alb\Inotify;

/**
 * StreamWrapper for inotify file descriptors
 *
 * It is useful to represent an inotify file descriptor as a stream for the
 * following reasons:
 *
 * - It is useable with stream_select(), stream_set_blocking()
 * - Prevents leaking the file descriptor
 */
final class StreamWrapper
{
    private ?int $fd;

    /**
     * @var resource
     */
    private mixed $stream;

    /**
     * @var resource
     */
    public mixed $context;

    /**
     * @param resource $stream
     */
    public static function fdFromStream(mixed $stream): int
    {
        $wrapper = \stream_get_meta_data($stream)['wrapper_data'] ?? null;

        if ($wrapper instanceof self) {
            return $wrapper->getFD();
        }

        return 0;
    }

    private function getFD(): int
    {
        if ($this->fd === null || $this->stream === null) {
            return 0;
        }

        return $this->fd;
    }

    public function dir_closedir(): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function dir_opendir(string $path, int $options): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function dir_readdir(): string|false
    {
        throw new UnsupportedStreamOperationException();
    }

    public function dir_rewinddir(): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function mkdir(string $path, int $mode, int $options): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function rename(string $path_from, string $path_to): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function rmdir(string $path, int $options): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    /**
     * @return resource
     */
    public function stream_cast(int $cast_as): mixed
    {
        return match ($cast_as) {
            STREAM_CAST_FOR_SELECT => $this->stream,
            default => throw new UnsupportedStreamOperationException(\sprintf('stream_cast is not supported for %d', $cast_as)),
        };
    }

    public function stream_close(): void
    {
        \fclose($this->stream);
        Inotify::closeFD($this->getFD());
    }

    // called at least by stream_get_meta_data()
    public function stream_eof(): bool
    {
        return false;
    }

    public function stream_flush(): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_lock(int $operation): bool
    {
        return \flock($this->stream, $operation);
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $fd = \substr($path, \strlen('inotify://'));
        if (!\is_numeric($fd)) {
            \trigger_error(\sprintf('Invalid file descriptor in "%s": "%s"', $path, $fd), E_USER_WARNING);

            return false;
        }

        $stream = fopen(\sprintf('php://fd/%d', $fd), 'r');
        if ($stream === false) {
            return false;
        }

        $this->fd = (int) $fd;
        $this->stream = $stream;

        return true;
    }

    public function stream_read(int $count): string|false
    {
        throw new UnsupportedStreamOperationException(\sprintf(
            'Buffered reads on an inotify stream not supported. Use %s\\read() instead.',
            __NAMESPACE__,
        ));
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_set_option(int $option, ?int $arg1, ?int $arg2): bool
    {
        return match ($option) {
            STREAM_OPTION_BLOCKING => \stream_set_blocking($this->stream, (bool) $arg1),
            default => false,
        };
    }

    public function stream_stat(): array
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_tell(): int
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_truncate(int $new_size): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_write(string $data): int
    {
        throw new UnsupportedStreamOperationException();
    }

    public function unlink(string $path): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function url_stat(string $path, int $flags): array
    {
        throw new UnsupportedStreamOperationException();
    }
}
