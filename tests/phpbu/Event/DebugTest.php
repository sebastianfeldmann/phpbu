<?php
namespace phpbu\App\Event;

use PHPUnit\Framework\TestCase;

/**
 * Debug test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class DebugTest extends TestCase
{
    /**
     * Tests Debug::getMessage
     */
    public function testGetResult()
    {
        $msg    = 'test';
        $debug  = new Debug($msg);
        $this->assertEquals('test', $debug->getMessage());
    }
}
