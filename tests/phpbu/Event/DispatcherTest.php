<?php
namespace phpbu\App\Event;

use PHPUnit\Framework\TestCase;

/**
 * Dispatcher test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class DispatcherTest extends TestCase
{
    /**
     * Tests Debug::getMessage
     */
    public function testDispatch()
    {
        $subscriber = $this->getMockBuilder(FakeSubscriber::class)
                           ->disableOriginalConstructor()
                           ->setMethods(['bar'])
                           ->getMock();

        $subscriber->expects($this->once())->method('bar');

        $dispatcher = new Dispatcher();
        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch('foo', 'baz');
    }
}
