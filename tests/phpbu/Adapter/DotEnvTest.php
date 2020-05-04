<?php
namespace phpbu\App\Adapter;

use PHPUnit\Framework\TestCase;

/**
 * Factory test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class DotenvTest extends TestCase
{
    /**
     * Tests DotEnv::setUp
     */
    public function testSetup()
    {
        $dotenv = new Dotenv();
        $dotenv->setup(['file' => PHPBU_TEST_FILES . '/misc/.envfoo']);

        $this->assertTrue(true);
    }

    /**
     * Tests DotEnv::getValue
     */
    public function testGetValue()
    {
        $dotenv = new Dotenv();
        $dotenv->setup(['file' => PHPBU_TEST_FILES . '/misc/.envbar']);

        $foo = $dotenv->getValue('DOT_ENV_BAR');
        $this->assertEquals('bar', $foo);
    }
}
