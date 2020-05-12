<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * Mcrypt ExecutableTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class McryptTest extends TestCase
{
    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testKeyAndAlgorithm()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $mcrypt->getCommand());
    }

    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testKeyFile()
    {
        $expected = 'mcrypt -u -f \'/foo/my.key\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useKeyFile('/foo/my.key')->useAlgorithm('blowfish')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $mcrypt->getCommand());
    }

    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testConfigFile()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' -c \'config.cnf\' \'/foo/bar.txt.nc\'';
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->useConfig('config.cnf')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $mcrypt->getCommand());
    }

    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testHash()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -h \'myHash\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->useHash('myHash')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $mcrypt->getCommand());
    }

    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useAlgorithm('blowfish')->useHash('myHash');

        $mcrypt->getCommand();
    }

    /**
     * Tests Mcrypt::createCommandLine
     */
    public function testNoKey()
    {
        $this->expectException('phpbu\App\Exception');
        $mcrypt   = new Mcrypt(PHPBU_TEST_BIN);
        $mcrypt->useAlgorithm('blowfish')->useHash('myHash')->saveAt('/foo/bar.txt.nc');

        $mcrypt->getCommand();
    }
}
