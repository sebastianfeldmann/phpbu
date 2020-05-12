<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * InfluxdumpTest ExecutableTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class InfluxdumpTest extends TestCase
{
    /**
     * Tests Influxdump::getCommand
     */
    public function testDefault()
    {
        $influxd = new Influxdump(PHPBU_TEST_BIN);
        $cmd       = $influxd->getCommand();
        $this->assertEquals(PHPBU_TEST_BIN . '/influxd backup -portable /tmp/influxdump', $cmd);
    }

    /**
     * Tests Influxdump::getCommandPrintable
     */
    public function testDefaultPrintable()
    {
        $influxd = new Influxdump(PHPBU_TEST_BIN);
        $cmd       = $influxd->getCommandPrintable();

        $this->assertEquals(PHPBU_TEST_BIN . '/influxd backup -portable /tmp/influxdump', $cmd);
    }

    /**
     * Tests Influxdump::useHost
     */
    public function testUseHost()
    {
        $influxd = new Influxdump(PHPBU_TEST_BIN);
        $influxd->useHost('localhost:8088');
        $cmd       = $influxd->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/influxd backup -portable -host=\'localhost:8088\' /tmp/influxdump', $cmd);
    }
}
