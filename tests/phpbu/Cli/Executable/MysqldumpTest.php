<?php
namespace phpbu\App\Cli\Executable;
use phpbu\App\Backup\Target\Compression;

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
class MysqldumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testDefault()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLinePrintable
     */
    public function testDefaultPrintable()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $cmd       = $mysqldump->getCommandLinePrintable();

        $this->assertEquals($path . '/mysqldump --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::dumpBlobsHexadecimal
     */
    public function testHexBlob()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpBlobsHexadecimal(true);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --hex-blob --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testPassword()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->credentials('foo', 'bar');
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --user=\'foo\' --password=\'bar\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testPasswordPrintable()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->credentials('foo', 'bar');
        $cmd       = $mysqldump->getCommandLinePrintable();

        $this->assertEquals($path . '/mysqldump --user=\'foo\' --password=\'******\' --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testLockTables()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->lockTables(true);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --lock-tables --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::useExtendedInsert
     */
    public function testUseExtendedInsert()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->useExtendedInsert(true);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump -e --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testTables()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpDatabases(['fiz']);
        $mysqldump->dumpTables(['foo', 'bar']);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump \'fiz\' --tables \'foo\' \'bar\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testTablesNoDatabase()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpTables(['foo', 'bar']);
        $cmd       = $mysqldump->getCommandLine();
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testSingleDatabase()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpDatabases(['foo']);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump \'foo\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testDatabases()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpDatabases(['foo', 'bar']);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --databases \'foo\' \'bar\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testIgnoreTables()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->ignoreTables(['foo', 'bar']);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\'', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testNoData()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpNoData(true);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --all-databases --no-data', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testCompressor()
    {
        $path        = realpath(__DIR__ . '/../../../_files/bin');
        $compression = Compression\Factory::create($path . '/gzip');
        $mysqldump   = new Mysqldump($path);
        $mysqldump->compressOutput($compression)->dumpTo('/tmp/foo.mysql');

        $this->assertEquals(
            $path . '/mysqldump --all-databases | ' . $path . '/gzip > /tmp/foo.mysql.gz',
            $mysqldump->getCommandLine()
        );
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testStructureOnly()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpStructureOnly(['foo', 'bar']);
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals(
            '(' . $path . '/mysqldump --all-databases --no-data'
          . ' && '
          . $path . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\''
          . ' --skip-add-drop-table --no-create-db --no-create-info)',
            $cmd
        );
    }

    /**
     * Tests Abstraction::unlinkErrorFile
     */
    public function testUnlinkFile()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $tmpFile   = tempnam(sys_get_temp_dir(), 'foo');

        $this->assertTrue(file_exists($tmpFile));
        $mysqldump->unlinkErrorFile($tmpFile);
        $this->assertFalse(file_exists($tmpFile));
    }

    /**
     * Tests Abstraction::run
     */
    public function testRunOk()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);

        $result = $this->getMockBuilder('\\phpbu\\App\\Cli\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();
        $result->method('getCode')->willReturn(0);

        $process = $this->getMockBuilder('\\phpbu\\App\\Cli\\Process')
                       ->disableOriginalConstructor()
                       ->getMock();
        $process->method('run')->willReturn($result);

        $mysqldump->setProcess($process);

        $res = $mysqldump->run();

        $this->assertEquals(0, $res->getCode());
    }

    /**
     * Tests Abstraction::run
     */
    public function testRunFail()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);

        $result = $this->getMockBuilder('\\phpbu\\App\\Cli\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();
        $result->method('getCode')->willReturn(1);

        $process = $this->getMockBuilder('\\phpbu\\App\\Cli\\Process')
                        ->disableOriginalConstructor()
                        ->getMock();
        $process->method('run')->willReturn($result);
        $process->method('isOutputRedirected')->willReturn(true);
        $process->method('getRedirectPath')->willReturn('/tmp/foo.txt');

        $mysqldump->setProcess($process);

        $res = $mysqldump->run();

        $this->assertEquals(1, $res->getCode());
    }
}
