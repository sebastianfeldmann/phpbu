<?php
namespace phpbu\App\Util;

use PHPUnit\Framework\TestCase;

/**
 * Time utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.2
 */
class TimeTest extends TestCase
{
    /**
     * Tests Time::timeSinceExecutionStart
     */
    public function testTimeSinceExecutionStartFail()
    {
        $this->expectException('RuntimeException');
        $SERVER = $_SERVER;
        unset($_SERVER['REQUEST_TIME_FLOAT']);
        unset($_SERVER['REQUEST_TIME']);

        try {
            $time = Time::timeSinceExecutionStart();
        } catch (\Exception $e) {
            $_SERVER = $SERVER;
            throw $e;
        }

        $this->assertFalse($time);
    }

    /**
     * Tests Time::timeSinceExecutionStart
     */
    public function testTimeSinceExecutionStartFloat()
    {
        $SERVER = $_SERVER;
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - 60;
        $time = Time::timeSinceExecutionStart();
        $this->assertEquals(60, floor($time));
        $_SERVER = $SERVER;
    }

    /**
     * Tests Time::timeSinceExecutionStart
     */
    public function testTimeSinceExecutionStart()
    {
        $SERVER = $_SERVER;
        unset($_SERVER['REQUEST_TIME_FLOAT']);
        $_SERVER['REQUEST_TIME'] = time() - 60;
        $time = Time::timeSinceExecutionStart();
        $this->assertEquals(60, floor($time));
        $_SERVER = $SERVER;
    }

    /**
     * Tests Time::formatTime
     */
    public function testFormatTime()
    {
        $this->assertEquals('37 seconds', Time::formatTime(37));
        $this->assertEquals('1 hour 1 second', Time::formatTime(3601));
        $this->assertEquals('1 hour 1 minute 1 second', Time::formatTime(3661));
        $this->assertEquals('2 hours 2 minutes 2 seconds', Time::formatTime(7322));
        $this->assertEquals('1 hour', Time::formatTime(3600));
        $this->assertEquals('1 minute', Time::formatTime(60));
        $this->assertEquals('1 second', Time::formatTime(1));
    }
}
