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
class RsyncTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Rsync::createCommandLine
     */
    public function testGetExecWithCustomArgs()
    {
        $expected = 'rsync --foo --bar';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->useArgs('--foo --bar');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testMinimal()
    {
        $expected = 'rsync -av \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testWithCompression()
    {
        $expected = 'rsync -avz \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->compressed(true);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testDelete()
    {
        $expected = 'rsync -av --delete \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->removeDeleted(true);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testExcludes()
    {
        $expected = 'rsync -av --exclude=\'fiz\' --exclude=\'buz\' \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->exclude(['fiz', 'buz']);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }


    /**
     * Tests Rsync::createCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoSource()
    {
        $rsync = new Rsync(PHPBU_TEST_BIN);
        $rsync->getCommandLine();
    }

    /**
     * Tests Rsync::createCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoTarget()
    {
        $rsync = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo');
        $rsync->getCommandLine();
    }
}
