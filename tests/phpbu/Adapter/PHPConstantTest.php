<?php
namespace phpbu\App\Adapter;

use PHPUnit\Framework\TestCase;

/**
 * Env test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0.11
 */
class PHPConstantTest extends TestCase
{
    /**
     * Tests PHPConstant::setup
     */
    public function testSetup()
    {
        $arr = new PHPConstant();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/empty.php']);

        $this->assertTrue(true);
    }

    /**
     * Tests PHPConstant::setup
     */
    public function testSetupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new PHPConstant();
        $arr->setup(['file' => 'constant.config.php']);
    }

    /**
     * Tests PHPConstant::getValue
     */
    public function testGetValue()
    {
        $arr = new PHPConstant();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/constant.config.php']);

        $fiz = $arr->getValue('PHPBU_TEST_ADAPTER');

        $this->assertEquals('fiz', $fiz);
    }


    /**
     * Tests PHPConstant::getValue
     */
    public function testGetValueFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new PHPConstant();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/empty.php']);

        $arr->getValue('FOO_BAR_BAZ');
    }
}
