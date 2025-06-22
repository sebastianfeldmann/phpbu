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
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0.
 */
interface Subscriber
{
    /**
     * Should return a event map like
     *   [
     *      'event-name' => 'method-to-call'
     *   ]
     *
     * @return array
     */
    public static function getSubscribedEvents(): array;
}
