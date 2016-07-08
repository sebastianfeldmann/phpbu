<?php
namespace phpbu\App\Cli\Executable;

/**
 * Rsync Executable Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class RsyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Rsync::createProcess
     */
    public function testGetExecWithCustomArgs()
    {
        $expected = 'rsync --foo --bar';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $rsync    = new Rsync($path);
        $rsync->useArgs('--foo --bar');

        $this->assertEquals($path . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testMinimal()
    {
        $expected = 'rsync -av \'./foo\' \'/tmp\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $rsync    = new Rsync($path);
        $rsync->fromPath('./foo')->toPath('/tmp');

        $this->assertEquals($path . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testWithCompression()
    {
        $expected = 'rsync -avz \'./foo\' \'/tmp\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $rsync    = new Rsync($path);
        $rsync->fromPath('./foo')->toPath('/tmp')->compressed(true);

        $this->assertEquals($path . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testDelete()
    {
        $expected = 'rsync -av --delete \'./foo\' \'/tmp\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $rsync    = new Rsync($path);
        $rsync->fromPath('./foo')->toPath('/tmp')->removeDeleted(true);

        $this->assertEquals($path . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testExcludes()
    {
        $expected = 'rsync -av --exclude=\'fiz\' --exclude=\'buz\' \'./foo\' \'/tmp\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $rsync    = new Rsync($path);
        $rsync->fromPath('./foo')->toPath('/tmp')->exclude(array('fiz', 'buz'));

        $this->assertEquals($path . '/' . $expected, $rsync->getCommandLine());
    }


    /**
     * Tests Rsync::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoSource()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $rsync = new Rsync($path);
        $rsync->getCommandLine();
    }

    /**
     * Tests Rsync::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoTarget()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $rsync = new Rsync($path);
        $rsync->fromPath('./foo');
        $rsync->getCommandLine();
    }
}
