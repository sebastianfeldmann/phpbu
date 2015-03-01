<?php
namespace phpbu\Backup\Cli;

/**
 * ExecTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class ExecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Exec::getExec
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGetExecFail()
    {
        $exec = new Exec();
        $exec->getExec();

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Cmd::addArgument
     */
    public function testGetExecOk()
    {
        $cmd = new Cmd('foo');
        $cmd->addArgument(array('bar', 'baz'));

        $exec = new Exec();
        $exec->addCommand($cmd);

        $res = $exec->getExec();

        $this->assertEquals('foo \'bar\' \'baz\'', $res, 'command should be as planned');
    }
}
