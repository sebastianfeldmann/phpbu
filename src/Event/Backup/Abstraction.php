<?php
namespace phpbu\App\Event\Backup;

use phpbu\App\Configuration\Backup;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    public function getConfiguration()
    {
        return $this->backup;
    }
}
