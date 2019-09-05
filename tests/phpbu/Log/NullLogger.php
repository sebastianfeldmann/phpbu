<?php
namespace phpbu\App\Log;

use phpbu\App\Listener;

/**
 * Class NullLogger
 *
 * @package phpbu\App
 */
class NullLogger implements Listener
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
