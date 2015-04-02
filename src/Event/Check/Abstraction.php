<?php

namespace phpbu\App\Event\Check;

use phpbu\App\Configuration\Backup\Check;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $check;

    public function __construct(Check $check)
    {
        $this->check = $check;
    }

    public function getConfiguration()
    {
        return $this->check;
    }
}
