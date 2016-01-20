<?php
namespace phpbu\App\Cli\Executable;

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
class McryptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Mcrypt::createProcess
     */
    public function testKeyAndAlgorithm()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals($path . '/' . $expected, $mcrypt->getCommandLine());
    }

    /**
     * Tests Mcrypt::createProcess
     */
    public function testKeyFile()
    {
        $expected = 'mcrypt -u -f \'/foo/my.key\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useKeyFile('/foo/my.key')->useAlgorithm('blowfish')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals($path . '/' . $expected, $mcrypt->getCommandLine());
    }

    /**
     * Tests Mcrypt::createProcess
     */
    public function testConfigFile()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' -c \'config.cnf\' \'/foo/bar.txt.nc\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->useConfig('config.cnf')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals($path . '/' . $expected, $mcrypt->getCommandLine());
    }

    /**
     * Tests Mcrypt::createProcess
     */
    public function testHash()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -h \'myHash\' -a \'blowfish\' \'/foo/bar.txt.nc\'';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useKey('fooBarBaz')->useAlgorithm('blowfish')->useHash('myHash')->saveAt('/foo/bar.txt.nc');

        $this->assertEquals($path . '/' . $expected, $mcrypt->getCommandLine());
    }

    /**
     * Tests Mcrypt::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoTarget()
    {
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useAlgorithm('blowfish')->useHash('myHash');

        $mcrypt->getCommandLine();
    }

    /**
     * Tests Mcrypt::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoKey()
    {
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt   = new Mcrypt($path);
        $mcrypt->useAlgorithm('blowfish')->useHash('myHash')->saveAt('/foo/bar.txt.nc');

        $mcrypt->getCommandLine();
    }
}
