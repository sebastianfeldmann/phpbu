<?php
namespace phpbu\App\Cli;

use SebastianFeldmann\Cli\Command\Result as CommandResult;
use PHPUnit\Framework\TestCase;

/**
 * ResultTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class ResultTest extends TestCase
{
    /**
     * Tests Result::getCmdResult
     */
    public function testGetEmptyPrintableCmd()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals('', $res->getCmdPrintable());
    }

    /**
     * Tests Result::getCmdResult
     */
    public function testGetPrintableCmd()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd, 'foo/bar');
        $this->assertEquals('foo/bar', $res->getCmdPrintable());
    }

    /**
     * Tests Result::isSuccessful
     */
    public function testIsSuccessful()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertTrue($res->isSuccessful());
    }

    /**
     * Tests Result::getReturnCode
     */
    public function testGetReturnCode()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals(0, $res->getReturnCode());
    }

    /**
     * Tests Result::getCmd
     */
    public function testGetCmd()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals('echo 1', $res->getCmd());
    }

    /**
     * Tests Result::getStdOut
     */
    public function testGetStdOut()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals("a\nb", $res->getStdOut());
    }

    /**
     * Tests Result::getStdErr
     */
    public function testGetStdErr()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb", "foo");
        $res = new Result($cmd);
        $this->assertEquals('foo', $res->getStdErr());
    }

    /**
     * Tests Result::isOutputRedirected
     * Tests Result::getRedirectPath
     */
    public function testIsOutputRedirectedTrue()
    {
        $cmd    = new CommandResult('echo 1', 0, 'foo', '', '/foo/bar.txt');
        $result = new Result($cmd);
        $this->assertTrue($result->isOutputRedirected());
        $this->assertEquals('/foo/bar.txt', $result->getRedirectPath());
    }

    /**
     * Tests Result::isOutputRedirected
     * Tests Result::getRedirectPath
     */
    public function testIsOutputRedirectedFalse()
    {
        $cmd    = new CommandResult('echo 1', 0, 'foo');
        $result = new Result($cmd);
        $this->assertFalse($result->isOutputRedirected());
        $this->assertEquals('', $result->getRedirectPath());
    }

    /**
     * Tests Result::getCommandResult
     */
    public function testGetCommandResult()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals($cmd, $res->getCommandResult());
    }

    /**
     * Tests Result::getOutput
     */
    public function testGetBufferedOutput()
    {
        $cmd = new CommandResult('echo 1', 0, "a\nb");
        $res = new Result($cmd);
        $this->assertEquals(['a', 'b'], $res->getBufferedOutput());
    }
}
