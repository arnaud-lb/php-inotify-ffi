<?php

declare(strict_types=1);

use Alb\Inotify\Inotify as i;

if (!defined('IN_ACCESS')) { define('IN_ACCESS', i::IN_ACCESS); }
if (!defined('IN_MODIFY')) { define('IN_MODIFY', i::IN_MODIFY); }
if (!defined('IN_ATTRIB')) { define('IN_ATTRIB', i::IN_ATTRIB); }
if (!defined('IN_CLOSE_WRITE')) { define('IN_CLOSE_WRITE', i::IN_CLOSE_WRITE); }
if (!defined('IN_CLOSE_NOWRITE')) { define('IN_CLOSE_NOWRITE', i::IN_CLOSE_NOWRITE); }
if (!defined('IN_OPEN')) { define('IN_OPEN', i::IN_OPEN); }
if (!defined('IN_MOVED_FROM')) { define('IN_MOVED_FROM', i::IN_MOVED_FROM); }
if (!defined('IN_MOVED_TO')) { define('IN_MOVED_TO', i::IN_MOVED_TO); }
if (!defined('IN_CREATE')) { define('IN_CREATE', i::IN_CREATE); }
if (!defined('IN_DELETE')) { define('IN_DELETE', i::IN_DELETE); }
if (!defined('IN_DELETE_SELF')) { define('IN_DELETE_SELF', i::IN_DELETE_SELF); }
if (!defined('IN_MOVE_SELF')) { define('IN_MOVE_SELF', i::IN_MOVE_SELF); }
if (!defined('IN_UNMOUNT')) { define('IN_UNMOUNT', i::IN_UNMOUNT); }
if (!defined('IN_Q_OVERFLOW')) { define('IN_Q_OVERFLOW', i::IN_Q_OVERFLOW); }
if (!defined('IN_IGNORED')) { define('IN_IGNORED', i::IN_IGNORED); }
if (!defined('IN_CLOSE')) { define('IN_CLOSE', i::IN_CLOSE); }
if (!defined('IN_MOVE')) { define('IN_MOVE', i::IN_MOVE); }
if (!defined('IN_ONLYDIR')) { define('IN_ONLYDIR', i::IN_ONLYDIR); }
if (!defined('IN_DONT_FOLLOW')) { define('IN_DONT_FOLLOW', i::IN_DONT_FOLLOW); }
if (!defined('IN_EXCL_UNLINK')) { define('IN_EXCL_UNLINK', i::IN_EXCL_UNLINK); }
if (!defined('IN_MASK_ADD')) { define('IN_MASK_ADD', i::IN_MASK_ADD); }
if (!defined('IN_ISDIR')) { define('IN_ISDIR', i::IN_ISDIR); }
if (!defined('IN_ONESHOT')) { define('IN_ONESHOT', i::IN_ONESHOT); }
if (!defined('IN_ALL_EVENTS')) { define('IN_ALL_EVENTS', i::IN_ALL_EVENTS); }

if (!function_exists('inotify_init')) {
    function inotify_init(): mixed {
        return i::inotifyInit();
    }
}

if (!function_exists('inotify_add_watch')) {
    function inotify_add_watch(mixed $inotify_instance, string $pathname, int $mask): int|false {
        return i::inotifyAddWatch($inotify_instance, $pathname, $mask);
    }
}

if (!function_exists('inotify_queue_len')) {
    function inotify_queue_len(mixed $inotify_instance): int {
        return i::inotifyQueueLen($inotify_instance);
    }
}

if (!function_exists('inotify_read')) {
    function inotify_read(mixed $inotify_instance): array|false {
        return i::inotifyRead($inotify_instance);
    }
}

if (!function_exists('inotify_rm_watch')) {
    function inotify_rm_watch(mixed $inotify_instance, int $watch_descriptor): bool {
        return i::inotifyRmWatch($inotify_instance, $watch_descriptor);
    }
}
