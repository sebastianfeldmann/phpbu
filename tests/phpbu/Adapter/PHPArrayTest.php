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
 * @since      Class available since Release 4.0.0
 */
class PHPArrayTest extends TestCase
{
    /**
     * Tests PHPArray::setup
     */
    public function testSetup()
    {
        $arr = new PHPArray();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/array.config.php']);

        $this->assertTrue(true);
    }

    /**
     * Tests PHPArray::setup
     */
    public function testSetupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new PHPArray();
        $arr->setup(['file' => 'array.config.php']);
    }

    /**
     * Tests PHPArray::getValue
     */
    public function testGetValue()
    {
        $arr = new PHPArray();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/array.config.php']);

        $fiz = $arr->getValue('foo.bar.baz');

        $this->assertEquals('fiz', $fiz);
    }


    /**
     * Tests PHPArray::getValue
     */
    public function testGetValueFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new PHPArray();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/array.config.php']);

        $arr->getValue('foo.bar.fiz');
    }
}
