<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration\Bootstrapper;
use PHPUnit\Framework\TestCase;

/**
 * Loader Factory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class FactoryTest extends TestCase
{
    /**
     * Tests Factory::createLoader
     */
    public function testCreateLoader()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-valid.json';
        $loader = Factory::createLoader($file, new Bootstrapper());

        $this->assertInstanceOf(Json::class, $loader);
    }

    /**
     * Tests Factory::createLoader
     */
    public function testCreateLoaderNoBootstrapper()
    {
        $file   = PHPBU_TEST_FILES . '/conf/json/config-valid.json';
        $loader = Factory::createLoader($file);

        $this->assertInstanceOf(Json::class, $loader);
    }
}
