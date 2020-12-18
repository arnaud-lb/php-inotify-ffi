<?php

namespace Alb\Inotify;

class Event
{
    public function __construct(
        private int $wd,
        private int $mask,
        private int $cookie,
        private ?string $name,
    ) {
    }

    public function getWatchDescriptor(): int
    {
        return $this->wd;
    }

    public function getMask(): int
    {
        return $this->mask;
    }

    public function getCookie(): int
    {
        return $this->cookie;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
