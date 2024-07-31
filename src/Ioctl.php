<?php

declare(strict_types=1);

namespace Alb\Inotify;

final class Ioctl
{
    public const FIONREAD = 0x541B;

    private static \FFI $ffi;

    private function __construct()
    {
    }

    public static function getFFI(): \FFI
    {
        return self::$ffi ??= \FFI::cdef('int ioctl(int, unsigned long, ...);');
    }

    public static function ioctl(int $fd, int $request, mixed ...$values): mixed
    {
        return self::getFFI()->ioctl($fd, $request, ...$values);
    }
}
