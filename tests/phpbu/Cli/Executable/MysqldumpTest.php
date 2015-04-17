<?php
namespace phpbu\App\Cli\Executable;

/**
 * Mysqldump Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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

        $this->assertEquals($path . '/mysqldump --all-databases 2> /dev/null', $cmd);
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

        $this->assertEquals($path . '/mysqldump --hex-blob --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testTables()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpTables(array('foo', 'bar'));
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --tables \'foo\' \'bar\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testDatabases()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpDatabases(array('foo', 'bar'));
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --databases \'foo\' \'bar\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testIgnoreTables()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->ignoreTables(array('foo', 'bar'));
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals($path . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\' 2> /dev/null', $cmd);
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

        $this->assertEquals($path . '/mysqldump --all-databases --no-data 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getCommandLine
     */
    public function testStructureOnly()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $mysqldump = new Mysqldump($path);
        $mysqldump->dumpStructureOnly(array('foo', 'bar'));
        $cmd       = $mysqldump->getCommandLine();

        $this->assertEquals(
            '(' . $path . '/mysqldump --all-databases --no-data 2> /dev/null'
          . ' && '
          . $path . '/mysqldump --all-databases --ignore-table=\'foo\' --ignore-table=\'bar\''
          . ' --skip-add-drop-table --no-create-db --no-create-info 2> /dev/null)',
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
