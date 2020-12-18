# php-inotify-ffi

php-inotify-ffi is a pure-PHP inotify binding based on FFI.

## Experimental / WIP

This is an experimental / WIP package. For a stable inotify binding, use [https://github.com/arnaud-lb/php-inotify](php-inotify).

## Goal

The goal of this package is to expose the raw inotify API to PHP, while being memory safe and preventing resource leaks.

## Streams

As the C inotify API returns file descriptors, this package returns PHP streams.

This is useful for the following reasons:

### I/O Polling

The streams can be used with polling mechanisms such as ``stream_select()`` or event loops such as ReactPHP or AMPHP. It's also possible to make the streams unblocking with ``stream_set_blocking()``.

### Resource management

As the inotify file descriptors are owned by PHP streams, they are managed by PHP. This ensures that the file descriptors are eventually closed, which prevents descriptor leaks.

## Comparison with the PECL extension

The [https://github.com/arnaud-lb/php-inotify](extension) is stable and has the same capabilities.
