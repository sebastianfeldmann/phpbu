<?php
namespace phpbu\App\Cli;

/**
 * Process Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Process::getCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGetExecFail()
    {
        $exec = new Process();
        $exec->getCommandLine();
    }

    /**
     * Tests Process::getCommandLine
     */
    public function testGetCommandLine()
    {
        $cmd = new Cmd('echo');
        $cmd->addArgument('foo');

        $process = new Process();
        $process->addCommand($cmd);

        $res = $process->getCommandLine();

        $this->assertEquals('echo \'foo\'', $res);
    }

    /**
     * Tests Process::getCommandLine
     */
    public function testGetCommandLineMultiCommand()
    {
        $cmd1 = new Cmd('echo');
        $cmd1->addArgument('foo');

        $cmd2 = new Cmd('echo');
        $cmd2->addArgument('bar');


        $process = new Process();
        $process->addCommand($cmd1);
        $process->addCommand($cmd2);

        $res = $process->getCommandLine();

        $this->assertEquals('(echo \'foo\' && echo \'bar\')', $res);
    }

    /**
     * Tests Process::isOutputRedirected
     */
    public function testRedirect()
    {
        $cmd = new Cmd('echo');
        $cmd->addArgument('foo');

        $process = new Process();
        $process->addCommand($cmd);

        $this->assertFalse($process->isOutputRedirected());

        $process->redirectOutputTo('/tmp/foo');

        $this->assertTrue($process->isOutputRedirected());
        $this->assertEquals('/tmp/foo', $process->getRedirectPath());
    }

    /**
     * Tests Process::run
     */
    public function testRun()
    {
        $cmd     = new Cmd('echo 1');
        $process = new Process();
        $process->addCommand($cmd);

        $res = $process->run();

        $this->assertEquals(0, $res->getCode(), 'echo should work everywhere');
    }
}
