<?php
namespace phpbu\App\Cli\Executable;

/**
 * Mysql Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 6.0-dev
 */
class MysqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Mysql::getCommand
     */
    public function testDefault()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql', $cmd);
    }

    /**
     * Tests Mysql::getCommandPrintable
     */
    public function testDefaultPrintable()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $cmd = $mysql->getCommandPrintable();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql', $cmd);
    }

    /**
     * Tests Mysql::getCommand
     */
    public function testPassword()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->credentials('foo', 'bar');
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --user=\'foo\' --password=\'bar\'', $cmd);
    }

    /**
     * Tests Mysql::getCommand
     */
    public function testPasswordPrintable()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->credentials('foo', 'bar');
        $original = $mysql->getCommand();
        $cmd = $mysql->getCommandPrintable();
        $restored = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --user=\'foo\' --password=\'bar\'', $original);
        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --user=\'foo\' --password=\'******\'', $cmd);
        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --user=\'foo\' --password=\'bar\'', $restored);
    }

    /**
     * Tests Mysql::useHost
     */
    public function testUseHost()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->useHost('localhost');
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --host=\'localhost\'', $cmd);
    }

    /**
     * Tests Mysql::usePort
     */
    public function testUsePort()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->usePort(1234);
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --port=\'1234\'', $cmd);
    }

    /**
     * Tests Mysql::useProtocol
     */
    public function testUseProtocol()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->useProtocol('TCP');
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --protocol=\'TCP\'', $cmd);
    }

    /**
     * Tests Mysql::useDatabase
     */
    public function testUseDatabase()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->useDatabase('some_database');
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --database=\'some_database\'', $cmd);
    }

    /**
     * Tests Mysql::useSourceFile
     */
    public function testUseSourceFile()
    {
        $mysql = new Mysql(PHPBU_TEST_BIN);
        $mysql->useSourceFile('fileToRestore.sql');
        $cmd = $mysql->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysql --execute=\'source fileToRestore.sql\'', $cmd);
    }
}
