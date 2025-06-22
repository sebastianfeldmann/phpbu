<?php
namespace phpbu\App\Adapter;

use PHPUnit\Framework\TestCase;

/**
 * Wordpress test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.12
 */
class WordPressTest extends TestCase
{
    /**
     * Tests Wordpress::setup
     */
    public function testSetup()
    {
        $arr = new WordPress();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/wp-config.php']);

        $this->assertTrue(true);
    }

    /**
     * Tests Wordpress::setup
     */
    public function testSetupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new WordPress();
        $arr->setup(['file' => 'wp-config.php']);
    }

    /**
     * Tests Wordpress::getValue
     */
    public function testGetValue()
    {
        $arr = new WordPress();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/wp-config.php']);

        $dbName = $arr->getValue('DB_NAME');

        $this->assertEquals('phpbu', $dbName);
    }


    /**
     * Tests Wordpress::getValue
     */
    public function testGetValueFail()
    {
        $this->expectException('phpbu\App\Exception');
        $arr = new WordPress();
        $arr->setup(['file' => PHPBU_TEST_FILES . '/misc/wp-config.php']);

        $arr->getValue('DB_FAIL');
    }
}
