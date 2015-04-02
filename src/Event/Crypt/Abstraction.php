<?php
namespace phpbu\App\Event\Crypt;

use phpbu\App\Configuration\Backup\Crypt;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $crypt;

    public function __construct(Crypt $crypt)
    {
        $this->crypt = $crypt;
    }

    public function getConfiguration()
    {
        return $this->crypt;
    }
}
