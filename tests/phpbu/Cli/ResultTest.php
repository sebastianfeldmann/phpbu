<?php
namespace phpbu\App\Cli;

/**
 * CmdTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Cmd::getCode
     */
    public function testGetCode()
    {
        $result = new Result('echo 1', 0, array());
        $this->assertEquals(0, $result->getCode(), 'code getter should work properly');
    }

    /**
     * Tests Cmd::wasSuccessful
     */
    public function testWasSuccessfulTrue()
    {
        $result = new Result('echo 1', 0, array());
        $this->assertEquals(true, $result->wasSuccessful(), 'should be successful on code 0');
    }

    /**
     * Tests Cmd::wasSuccessful
     */
    public function testWasSuccessfulFalse()
    {
        $result = new Result('echo 1', 1, array());
        $this->assertEquals(false, $result->wasSuccessful(), 'should not be successful on code 1');
    }

    /**
     * Tests Cmd::getCmd
     */
    public function testGetCmd()
    {
        $result = new Result('echo 1', 0, array());
        $this->assertEquals('echo 1', $result->getCmd(), 'cmd getter should work properly');
    }

    /**
     * Tests Cmd::getOutput
     */
    public function testGetOutput()
    {
        $result = new Result('echo 1', 0, array('foo', 'bar'));
        $this->assertEquals(2, count($result->getOutput()), 'output getter should work properly');
    }

    /**
     * Tests Cmd::getOutput
     */
    public function testGetOutputAsString()
    {
        $result = new Result('echo 1', 0, array('foo', 'bar'));
        $this->assertEquals('foo' . PHP_EOL . 'bar', $result->getOutputAsString(), 'outputAsString getter should work properly');
    }

    /**
     * Tests Cmd::__toString
     */
    public function testToString()
    {
        $result = new Result('echo 1', 0, array('foo'));
        $this->assertEquals('foo', (string) $result, 'toString should work properly');
    }
}
