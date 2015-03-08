<?php
namespace phpbu\App\Backup\Cli;

/**
 * CmdTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class CmdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Cmd::getName
     */
    public function testGetName()
    {
        $cmd = new Cmd('foo');

        $this->assertEquals('foo', $cmd->getName(), 'name getter should work properly');
    }

    /**
     * Tests Cmd::addArgument
     */
    public function testAddArgumentPlain()
    {
        $cmd = new Cmd('foo');
        $cmd->addArgument('bar');

        $this->assertEquals('foo \'bar\'', (string) $cmd, 'argument should be added');
    }

    /**
     * Tests Cmd::addArgument
     */
    public function testAddArgumentArray()
    {
        $cmd = new Cmd('foo');
        $cmd->addArgument(array('bar', 'baz'));

        $this->assertEquals('foo \'bar\' \'baz\'', (string) $cmd, 'arguments should be added');
    }
}
