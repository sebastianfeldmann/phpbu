<?php
namespace phpbu\App\Configuration;

use PHPUnit\Framework\TestCase;

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
class BootstrapperTest extends TestCase
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
     */
    public function testBootstrapWithOption()
    {
        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap_FAIL.php');

        $runner = new Bootstrapper(PHPBU_TEST_FILES . '/misc/bootstrap.option.php');
        $runner->run($configuration);

        $this->assertTrue(defined('BOOTSTRAP_OPTION_LOADED'), 'option constant should be defined');
    }

    /**
     * Tests Bootstrapper::run
     */
    public function testBootstrapNoFile()
    {
        $this->expectException('phpbu\App\Exception');
        $configuration = $this->createMock(\phpbu\App\Configuration::class);
        $configuration->expects($this->once())
                      ->method('getBootstrap')
                      ->willReturn(PHPBU_TEST_FILES . '/misc/bootstrap_FAIL.php');

        $runner = new Bootstrapper();
        $runner->run($configuration);
    }
}
