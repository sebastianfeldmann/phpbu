<?php declare(strict_types=1);

namespace phpbu\App\Event;

/**
 * Class Dispatcher
 *
 * @package    phpbu
 * @subpackage
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0.0.
 */
class Dispatcher
{
    /**
     * List of all listeners
     *
     * @var array
     */
    private $listeners = [];

    /**
     * Add subscriber that receives events
     *
     * @param \phpbu\App\Event\Subscriber $subscriber
     */
    public function addSubscriber(Subscriber $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $method) {
            $this->addListener($eventName, [$subscriber, $method]);
        }
    }

    /**
     * Dispatch an event to all its listeners
     *
     * @param  string $eventName
     * @param  mixed  $event
     * @return void
     */
    public function dispatch(string $eventName, $event): void
    {
        $listeners = $this->getListeners($eventName);
        $this->callListeners($listeners, $event);
    }

    /**
     * Add a subscriber to all relevant events
     *
     * @param  string $eventName
     * @param  array $listener
     * @return void
     */
    private function addListener(string $eventName, array $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Return list of all listeners
     *
     * @param  string $eventName
     * @return array
     */
    private function getListeners(string $eventName): array
    {
        if (empty($this->listeners[$eventName])) {
            return [];
        }
        return $this->listeners[$eventName];
    }

    /**
     * Call all the subscriber methods
     *
     * @param array  $listeners
     * @param mixed  $event
     * @return void
     */
    private function callListeners(array $listeners, $event): void
    {
        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
