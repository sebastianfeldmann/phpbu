<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;

/**
 * Bootstrap Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Bootstrap::run
     */
    public function testBootstrapOk()
    {
        $configuration = $this->getMockBuilder('\\phpbu\\App\\Configuration')
                              ->disableOriginalConstructor()
                              ->getMock();
        $configuration->expects($this->once())
                      ->method('getIniSettings')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getIncludePaths')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(__DIR__ . '/../../_files/misc/bootstrap.php');

        $runner = new Bootstrap();
        $runner->run($configuration);

        $this->assertTrue(defined('BOOTSTRAP_LOADED'), 'constant should be defined');
    }

    /**
     * Tests Bootstrap::run
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBootstrapNoFile()
    {
        $configuration = $this->getMockBuilder('\\phpbu\\App\\Configuration')
                              ->disableOriginalConstructor()
                              ->getMock();
        $configuration->expects($this->once())
                      ->method('getIniSettings')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getIncludePaths')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(__DIR__ . '/../../_files/misc/bootstrap_FAIL.php');

        $runner = new Bootstrap();
        $runner->run($configuration);
    }

    /**
     * Tests Bootstrap::run
     */
    public function testIncludePath()
    {
        $configuration = $this->getMockBuilder('\\phpbu\\App\\Configuration')
                              ->disableOriginalConstructor()
                              ->getMock();
        $configuration->expects($this->once())
                      ->method('getIniSettings')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getIncludePaths')
                      ->willReturn(['/FOO', '/BAR']);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(null);

        $old = ini_get('include_path');

        $runner = new Bootstrap();
        $runner->run($configuration);

        $this->assertTrue(strpos(ini_get('include_path'), 'FOO') !== false, '/FOO should be set');
        $this->assertTrue(strpos(ini_get('include_path'), 'BAR') !== false, '/FOO should be set');

        ini_set('include_path', $old);
    }

    /**
     * Tests Bootstrap::run
     */
    public function testIniSettings()
    {

        $old = ini_get('session.name');
        $new = 'FOO';

        $configuration = $this->getMockBuilder('\\phpbu\\App\\Configuration')
                             ->disableOriginalConstructor()
                             ->getMock();
        $configuration->expects($this->once())
                      ->method('getIniSettings')
                      ->willReturn(['session.name' => $new]);
        $configuration->expects($this->once())
                      ->method('getIncludePaths')
                      ->willReturn([]);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(null);


        $this->assertEquals($old, ini_get('session.name'), 'name should be PHPSESS');

        $runner = new Bootstrap();
        $runner->run($configuration);

        $this->assertEquals('FOO', ini_get('session.name'), 'name should be FOO');

        // restore the default ini setting
        ini_set('session.name', $old);
    }
}
