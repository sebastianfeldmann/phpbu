<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Exception;

/**
 * Mysqlimport Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0-dev
 */
class MysqlimportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Mysqlimport::getCommand
     */
    public function testNoSourceAndTarget()
    {
        $this->expectException(Exception::class);

        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->getCommand();
    }

    /**
     * Tests Mysqlimport::getCommand
     */
    public function testDefault()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $cmd = $mysqlimport->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\'', $cmd);
    }

    /**
     * Tests Mysqlimport::getCommandPrintable
     */
    public function testDefaultPrintable()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $cmd = $mysqlimport->getCommandPrintable();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\'', $cmd);
    }

    /**
     * Tests Mysqlimport::getCommand
     */
    public function testPassword()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $mysqlimport->credentials('foo', 'bar');
        $cmd = $mysqlimport->getCommand();

        $expected = PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --user=\'foo\' --password=\'bar\'';
        $this->assertEquals($expected, $cmd);
    }

    /**
     * Tests Mysqlimport::getCommand
     */
    public function testPasswordPrintable()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $mysqlimport->credentials('foo', 'bar');
        $original = $mysqlimport->getCommand();
        $cmd = $mysqlimport->getCommandPrintable();
        $restored = $mysqlimport->getCommand();

        $expected1 = PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --user=\'foo\' --password=\'bar\'';
        $expected2 = PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --user=\'foo\' --password=\'******\'';
        $this->assertEquals($expected1, $original);
        $this->assertEquals($expected2, $cmd);
        $this->assertEquals($expected1, $restored);
    }

    /**
     * Tests Mysqlimport::useHost
     */
    public function testUseHost()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $mysqlimport->useHost('localhost');
        $cmd = $mysqlimport->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --host=\'localhost\'', $cmd);
    }

    /**
     * Tests Mysqlimport::usePort
     */
    public function testUsePort()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $mysqlimport->usePort(1234);
        $cmd = $mysqlimport->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --port=\'1234\'', $cmd);
    }

    /**
     * Tests Mysqlimport::useProtocol
     */
    public function testUseProtocol()
    {
        $mysqlimport = new Mysqlimport(PHPBU_TEST_BIN);
        $mysqlimport->setSourceAndTarget('source.sql', 'database');
        $mysqlimport->useProtocol('TCP');
        $cmd = $mysqlimport->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqlimport \'database\' \'source.sql\' --protocol=\'TCP\'', $cmd);
    }
}
