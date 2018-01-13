<?php
namespace phpbu\App\Configuration;

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
class BootstrapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Bootstrapper::run
     */
    public function testBootstrapOk()
    {
        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap.php');

        $runner = new Bootstrapper();
        $runner->run($configuration);

        $this->assertTrue(defined('BOOTSTRAP_LOADED'), 'constant should be defined');
    }

    /**
     * Tests Bootstrapper::run
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBootstrapNoFile()
    {
        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap_FAIL.php');

        $runner = new Bootstrapper();
        $runner->run($configuration);
    }
}
