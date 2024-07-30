<?php

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
class StreamWrapper
{
    private ?int $fd;

    private $stream;

    public $context;

    public static function fdFromStream($stream): int
    {
        $meta = stream_get_meta_data($stream);
        if (!$meta['wrapper_data'] instanceof self) {
            throw new \InvalidArgumentException('Unsupported stream: "%s"', $meta['uri']);
        }

        return $meta['wrapper_data']->getFD();
    }

    public function dir_closedir(): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function dir_opendir(string $path, int $options): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function dir_readdir(): string
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

    public function stream_cast(int $cast_as)
    {
        switch ($cast_as) {
        case STREAM_CAST_FOR_SELECT:
            return $this->stream;
        default:
            throw new UnsupportedStreamOperationException(sprintf(
                'stream_cast is not supported for %d',
                $cast_as,
            ));
        }
    }

    public function stream_close(): void
    {
        fclose($this->stream);
        $this->closeFD();
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
        return flock($this->stream, $operation);
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        throw new UnsupportedStreamOperationException();
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $fd = substr($path, strlen('inotify://'));
        if (!is_numeric($fd)) {
            throw new Exception(sprintf(
                'Invalid file descriptor in "%s": "%s"',
                $path,
                $fd,
            ));
        }

        $stream = fopen(sprintf('php://fd/%d', $fd), 'r');
        if ($stream === false) {
            throw new Exception('Failed opening fd as a stream');
        }

        $this->fd = $fd;
        $this->stream = $stream;

        return true;
    }

    public function stream_read(int $count): string
    {
        throw new UnsupportedStreamOperationException(sprintf(
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
        switch ($option) {
        case STREAM_OPTION_BLOCKING:
            return stream_set_blocking($this->stream, $arg1);
        default:
            return false;
        }
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

    private function getFD(): int
    {
        if ($this->fd === null || $this->stream === null) {
            throw new Exception('stream is not opened');
        }

        return $this->fd;
    }

    private function closeFD()
    {
        $ffi = init();
        $ffi->close($this->getFD());
    }
}
