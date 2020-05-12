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
class EnvTest extends TestCase
{
    /**
     * Tests Env::setup
     */
    public function testSetup()
    {
        $env = new Env();
        $env->setup([]);

        $this->assertTrue(true);
    }

    /**
     * Tests Env::getValue
     */
    public function testGetValue()
    {
        putenv('ENV_FOO=bar');

        $env = new Env();
        $foo = $env->getValue('ENV_FOO');

        $this->assertEquals('bar', $foo);
    }
}
