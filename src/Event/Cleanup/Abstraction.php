<?php
namespace phpbu\App\Event\Cleanup;

use phpbu\App\Configuration\Backup\Cleanup;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $cleanup;

    public function __construct(Cleanup $cleanup)
    {
        $this->cleanup = $cleanup;
    }

    public function getConfiguration()
    {
        return $this->cleanup;
    }
}
