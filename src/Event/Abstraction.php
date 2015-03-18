<?php

namespace phpbu\App\Event;

use Symfony\Component\EventDispatcher\Event;


abstract class Abstraction extends Event
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
