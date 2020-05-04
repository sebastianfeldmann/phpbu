<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

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
class RsyncTest extends TestCase
{
    /**
     * Tests Rsync::getCommandLine
     */
    public function testGetExecWithCustomArgs()
    {
        $expected = 'rsync --foo --bar';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->useArgs('--foo --bar');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testMinimal()
    {
        $expected = 'rsync -av \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testPassword()
    {
        $password = 'secret';
        $export   = 'RSYNC_PASSWORD=' . escapeshellarg($password) . ' ';
        $expected = 'rsync -av \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->usePassword($password)
              ->fromPath('./foo')
              ->toPath('/tmp');

        $this->assertEquals($export . PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandPrintable
     */
    public function testPasswordPrintable()
    {
        $password = 'secret';
        $env      = 'RSYNC_PASSWORD=\'******\' ';
        $expected = 'rsync -av \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->usePassword($password);
        $rsync->fromPath('./foo')->toPath('/tmp');

        $this->assertEquals($env . PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandPrintable());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testPasswordFile()
    {
        $expected = 'rsync -av --password-file=\'./.rsync-password\' \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->usePasswordFile('./.rsync-password')
              ->fromPath('./foo')
              ->toPath('/tmp');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testWithCompression()
    {
        $expected = 'rsync -avz \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->compressed(true);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testDelete()
    {
        $expected = 'rsync -av --delete \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->removeDeleted(true);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testExcludes()
    {
        $expected = 'rsync -av --exclude=\'fiz\' --exclude=\'buz\' \'./foo\' \'/tmp\'';
        $rsync    = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo')->toPath('/tmp')->exclude(['fiz', 'buz']);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $rsync->getCommandLine());
    }


    /**
     * Tests Rsync::getCommandLine
     */
    public function testNoSource()
    {
        $this->expectException('phpbu\App\Exception');
        $rsync = new Rsync(PHPBU_TEST_BIN);
        $rsync->getCommandLine();
    }

    /**
     * Tests Rsync::getCommandLine
     */
    public function testNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $rsync = new Rsync(PHPBU_TEST_BIN);
        $rsync->fromPath('./foo');
        $rsync->getCommandLine();
    }
}
