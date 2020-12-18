<?php

namespace Alb\Inotify;

use FFI;

// Supported events suitable for MASK parameter of INOTIFY_ADD_WATCH.

/** File was accessed. */
const IN_ACCESS = 0x00000001;

/** File was modified. */
const IN_MODIFY = 0x00000002;

/** Metadata changed. */
const IN_ATTRIB = 0x00000004;

/** Writtable file was closed. */
const IN_CLOSE_WRITE = 0x00000008;

/** Unwrittable file closed. */
const IN_CLOSE_NOWRITE = 0x00000010;

/** File was opened. */
const IN_OPEN = 0x00000020;

/** File was moved from X. */
const IN_MOVED_FROM = 0x00000040;

/** File was moved to Y. */
const IN_MOVED_TO = 0x00000080;

/** Subfile was created. */
const IN_CREATE = 0x00000100;

/** Subfile was deleted. */
const IN_DELETE = 0x00000200;

/** Self was deleted. */
const IN_DELETE_SELF = 0x00000400;

/** Self was moved. */
const IN_MOVE_SELF = 0x00000800;

// Events sent by the kernel.


/** Backing fs was unmounted. */
const IN_UNMOUNT = 0x00002000;

/** Event queued overflowed. */
const IN_Q_OVERFLOW = 0x00004000;

/** File was ignored. */
const IN_IGNORED = 0x00008000;

// Helper events.

/** Close. */
const IN_CLOSE = (IN_CLOSE_WRITE | IN_CLOSE_NOWRITE) ;

/** Moves. */
const IN_MOVE = (IN_MOVED_FROM | IN_MOVED_TO) ;

// Special flags.

/** Only watch the path if it is a directory. */
const IN_ONLYDIR = 0x01000000;

/** Do not follow a sym link. */
const IN_DONT_FOLLOW = 0x02000000;

/** Exclude events on unlinked objects. */
const IN_EXCL_UNLINK = 0x04000000;

/** Add to the mask of an already existing watch. */
const IN_MASK_ADD = 0x20000000;

/** Event occurred against dir. */
const IN_ISDIR = 0x40000000;

/** Only send event once. */
const IN_ONESHOT = 0x80000000;

/** All events which a program can wait on. */
const IN_ALL_EVENTS = (IN_ACCESS | IN_MODIFY | IN_ATTRIB | IN_CLOSE_WRITE | IN_CLOSE_NOWRITE | IN_OPEN | IN_MOVED_FROM | IN_MOVED_TO | IN_CREATE | IN_DELETE | IN_DELETE_SELF | IN_MOVE_SELF);

/** @internal */
const EAGAIN = 11;

/** @internal */
const EINVAL = 22;

/**
 * Initializes a new inotify instance.
 *
 * See "man 2 inotify_init".
 *
 * Returns a stream that is useable with the following functions:
 *
 * - inotify_add_watch()
 * - inotify_read()
 * - stream_select()
 * - stream_set_blocking()
 * - fclose()
 *
 * @return stream
 */
function inotify_init()
{
    $ffi = init();

    $fd = $ffi->inotify_init();
    if ($fd === -1) {
        throw new Exception('inotify_init: call failed', $fd);
    }

    $stream = fopen(sprintf('inotify://%d', $fd), 'r');
    if ($stream === false) {
        throw new Exception('inotify_init: failed creating stream');
    }

    return $stream;
}

/**
 * Adds a watch to an inotify stream
 *
 * Returns a watch descriptor (can be passed to inotify_rm_watch, can be
 * referenced by events returned from inotify_read)
 *
 * @return int
 */
function inotify_add_watch($stream, string $name, int $mask): int
{
    $ffi = init();
    $fd = StreamWrapper::fdFromStream($stream);

    $watchDescriptor = $ffi->inotify_add_watch($fd, $name, $mask);
    if ($watchDescriptor === -1) {
        throw new \Exception('inotify_add_watch: call failed', $watchDescriptor);
    }

    return $watchDescriptor;
}

function inotify_rm_watch($stream, int $watchDescriptor, int $mask): void
{
    $ffi = init();
    $fd = StreamWrapper::fdFromStream($stream);

    $ret = $ffi->inotify_rm_watch($fd);
    if ($ret === -1) {
        throw new \Exception('inotify_rm_watch: call failed', $watchDescriptor);
    }
}

/**
 * Read inotify events from $stream
 *
 * Returns immediately if the stream is non-blocking and no events are pending
 *
 * @return Event[]
 */
function inotify_read($stream): array
{
    $ffi = init();
    $fd = StreamWrapper::fdFromStream($stream);

    $inotifyEventType = $ffi->type('struct inotify_event');
    $inotifyEventPtrType = $ffi->type('struct inotify_event *');

    $bufSize = max(FFI::sizeof($inotifyEventType) + 255, 128);

    while (true) {
        $buf = $ffi->new(FFI::arrayType(FFI::type('char'), [$bufSize]));
        $readden = $ffi->read($fd, $buf, $bufSize);

        if ($readden === -1) {
            // buf too small to read an event
            if ($ffi->errno === EINVAL) {
                $bufSize = (int) ceil($buffize * 1.6);
                continue;
            }
            // fd is unblocking, and no event is available
            if ($ffi->errno === EAGAIN) {
                return [];
            }
        }

        break;
    }

    for ($i = 0; $i < $readden; $i += FFI::sizeof($inotifyEventType) + $event->len) {
        $event = $ffi->cast($inotifyEventPtrType, FFI::addr($buf[$i]));

        $events[] = new Event(
            $event->wd,
            $event->mask,
            $event->cookie,
            $event->len > 0 ? FFI::string($event->name) : null,
        );
    }

    return $events;
}

/** @internal */
function init(): FFI {
    static $ffi;

    if ($ffi !== null) {
        return $ffi;
    }

    $ffi = FFI::load(__DIR__ . '/inotify.h');

    stream_wrapper_register('inotify', StreamWrapper::class, STREAM_IS_URL);

    return $ffi;
}
