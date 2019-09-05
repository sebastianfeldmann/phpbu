<?php

namespace phpbu\App\Event;

/**
 * Class FakeSubscriber
 *
 * @package    phpbu
 * @subpackage
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release
 */
class FakeSubscriber implements Subscriber
{
    /**
     * Should return a event map like
     *   [
     *      'event-name' => 'method-to-call'
     *   ]
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return ['foo' => 'bar'];
    }

    /**
     * Fake event handling method
     *
     * @param $event
     */
    public function bar($event)
    {
        // this should be called
    }
}
