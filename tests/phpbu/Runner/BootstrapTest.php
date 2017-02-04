<?php
namespace phpbu\App\Runner;

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
class BootstrapTest extends \PHPUnit\Framework\TestCase
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
                      ->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap.php');

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
                      ->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap_FAIL.php');

        $runner = new Bootstrap();
        $runner->run($configuration);
    }
}
