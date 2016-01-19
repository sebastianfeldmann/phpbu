<?php
namespace phpbu\App\Configuration\Loader;

/**
 * Loader Factory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Factory::createLoader
     */
    public function testCreateLoader()
    {
        $file   = realpath(__DIR__ . '/../../../_files/conf/json/config-valid.json');
        $loader = Factory::createLoader($file);

        $this->assertTrue($loader instanceof Json);
    }
}
