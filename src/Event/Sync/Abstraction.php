<?php
namespace phpbu\App\Event\Sync;

use phpbu\App\Configuration\Backup\Sync;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $sync;

    public function __construct(Sync $sync)
    {
        $this->sync = $sync;
    }

    public function getConfiguration()
    {
        return $this->sync;
    }
}
