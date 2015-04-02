<?php
namespace phpbu\App\Event\App;

use phpbu\App\Configuration;
use Symfony\Component\EventDispatcher\Event;

abstract class Abstraction extends Event
{
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}
