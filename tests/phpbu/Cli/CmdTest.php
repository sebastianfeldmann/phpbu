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
 * @since      Class available since Release 2.1.0
 */
class CmdTest extends \PHPUnit_Framework_TestCase
{

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
     * Tests Cmd::silence
     */
    public function testSilence()
    {
        $cmd = new Cmd('foo');
        $cmd->silence(true);

        $this->assertEquals('foo 2> /dev/null', (string) $cmd, 'command should be silenced');
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

    /**
     * Tests Cmd::addOption
     */
    public function testAddOptionArray()
    {
        $cmd = new Cmd('foo');
        $cmd->addOption('-bar', array('fiz', 'baz'));

        $this->assertEquals('foo -bar \'fiz\' \'baz\'', (string) $cmd, 'arguments should be added');
    }

    /**
     * Tests Cmd::addOptionIfNotEmpty
     */
    public function testAddOptionIfEmpty()
    {
        $cmd = new Cmd('foo');
        $cmd->addOptionIfNotEmpty('-bar', '', false);

        $this->assertEquals('foo', (string) $cmd, 'option should not be added');

        $cmd->addOptionIfNotEmpty('-bar', 'fiz', false);

        $this->assertEquals('foo -bar', (string) $cmd, 'option should be added');
    }

    /**
     * Tests Cmd::addOptionIfNotEmpty
     */
    public function testAddOptionIfEmptyAsValue()
    {
        $cmd = new Cmd('foo');
        $cmd->addOptionIfNotEmpty('-bar', '');

        $this->assertEquals('foo', (string) $cmd, 'option should not be added');

        $cmd->addOptionIfNotEmpty('-bar', 'fiz');

        $this->assertEquals('foo -bar=\'fiz\'', (string) $cmd, 'option should be added');
    }
}
