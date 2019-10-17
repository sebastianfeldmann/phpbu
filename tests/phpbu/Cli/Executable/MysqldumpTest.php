<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Backup\Target\Compression;
use PHPUnit\Framework\TestCase;

/**
 * Mysqldump Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class MysqldumpTest extends TestCase
{
    /**
     * Tests Mysqldump::getCommand
     */
    public function testDefault()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandPrintable
     */
    public function testDefaultPrintable()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $cmd       = $mysqldump->getCommandPrintable();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::dumpBlobsHexadecimal
     */
    public function testHexBlob()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpBlobsHexadecimal(true);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --hex-blob --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testPassword()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->credentials('foo', 'bar');
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --user=\'foo\' --password=\'bar\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testPasswordPrintable()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->credentials('foo', 'bar');
        $original  = $mysqldump->getCommand();
        $cmd       = $mysqldump->getCommandPrintable();
        $restored  = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --user=\'foo\' --password=\'bar\' --all-databases', $original);
        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --user=\'foo\' --password=\'******\' --all-databases', $cmd);
        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --user=\'foo\' --password=\'bar\' --all-databases', $restored);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testLockTables()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->lockTables(true);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --lock-tables --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testSingleTransaction()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->singleTransaction(true);

        $cmd = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --single-transaction --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::skipExtendedInsert
     */
    public function testSkipExtendedInsert()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->skipExtendedInsert(true);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --skip-extended-insert --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::useHost
     */
    public function testUseHost()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->useHost('localhost');
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --host=\'localhost\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::usePort
     */
    public function testUsePort()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->usePort(1234);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --port=\'1234\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::useProtocol
     */
    public function testUseProtocol()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->useProtocol('TCP');
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --protocol=\'TCP\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testTables()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpDatabases(['fiz']);
        $mysqldump->dumpTables(['foo', 'bar']);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump \'fiz\' --tables \'foo\' \'bar\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testTablesNoDatabase()
    {
        $this->expectException('phpbu\App\Exception');
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpTables(['foo', 'bar']);
        $cmd       = $mysqldump->getCommand();
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testSingleDatabase()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpDatabases(['foo']);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump \'foo\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testDatabases()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpDatabases(['foo', 'bar']);
        $cmd       = $mysqldump->getCommand();

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --databases \'foo\' \'bar\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testIgnoreTables()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->ignoreTables(['foo', 'bar']);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\'',
            $mysqldump->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testNoData()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpNoData(true);

        $this->assertEquals($path . '/mysqldump --all-databases --no-data', $mysqldump->getCommand());
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testGTIDValid()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->addGTIDStatement('ON');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --set-gtid-purged=\'ON\' --all-databases',
            $mysqldump->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testGTIDInvalid()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->addGTIDStatement('FOO');

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --all-databases',
            $mysqldump->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testCompressor()
    {
        $path        = realpath(__DIR__ . '/../../../_files/bin');
        $compression = Compression\Factory::create($path . '/gzip');
        $mysqldump   = new Mysqldump($path);
        $mysqldump->compressOutput($compression)->dumpTo('/tmp/foo.mysql');

        $this->assertEquals(
            $path . '/mysqldump --all-databases | ' . $path . '/gzip > /tmp/foo.mysql.gz',
            $mysqldump->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testStructureOnly()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->dumpStructureOnly(['foo', 'bar']);

        $this->assertEquals(
            '(' . PHPBU_TEST_BIN . '/mysqldump --all-databases --no-data'
            . ' && '
            . PHPBU_TEST_BIN . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\''
            . ' --skip-add-drop-table --no-create-db --no-create-info)',
            $mysqldump->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getCommand
     */
    public function testSkipTriggers()
    {
        $mysqldump = new Mysqldump(PHPBU_TEST_BIN);
        $mysqldump->skipTriggers(true);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --skip-triggers --all-databases',
            $mysqldump->getCommand()
        );
    }
}
