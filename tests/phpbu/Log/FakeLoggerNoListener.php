<?php
namespace phpbu\App\Log;

/**
 * Class LoggerNoListener
 *
 * Dummy class to test phpbu Factory create methods.
 */
class FakeLoggerNoListener implements Logger
{
    /**
     * Setup the logger.
     *
     * @param array $options
     */
    public function setup(array $options)
    {
        // do something fooish
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
