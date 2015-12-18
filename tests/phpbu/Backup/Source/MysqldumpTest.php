<?php
namespace phpbu\App\Backup\Source;
use phpbu\App\Backup\CliTest;

/**
 * MysqldumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MysqldumpTest extends CliTest
{
    /**
     * Mysqldump
     *
     * @var \phpbu\App\Backup\Source\Mysqldump
     */
    protected $mysqldump;

    /**
     * Setup mysqldump
     */
    public function setUp()
    {
        $this->mysqldump = new Mysqldump();
    }

    /**
     * Clear mysqldump
     */
    public function tearDown()
    {
        $this->mysqldump = null;
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testDefault()
    {
        $target = $this->getTargetMock();
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path));

        $executable = $this->mysqldump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/mysqldump --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testLockTables()
    {
        $target = $this->getTargetMock();
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path, 'lockTables' => 'true'));

        $executable = $this->mysqldump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/mysqldump --lock-tables --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testHexBlob()
    {
        $target = $this->getTargetMock();
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path, 'hexBlob' => 'true'));

        $executable = $this->mysqldump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/mysqldump --hex-blob --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testExtendedInsert()
    {
        $target = $this->getTargetMock();
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path, 'extendedInsert' => 'true'));

        $executable = $this->mysqldump->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/mysqldump -e --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'mysqldump');
        $appResult = $this->getAppResultMock();
        $mysqldump = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mysqldump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $mysqldump->expects($this->once())->method('run')->willReturn($cliResult);

        $path = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path));
        $this->mysqldump->setExecutable($mysqldump);
        $status = $this->mysqldump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mysqldump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'mysqldump');
        $appResult = $this->getAppResultMock();
        $mysqldump = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mysqldump')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $mysqldump->expects($this->once())->method('run')->willReturn($cliResult);

        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $this->mysqldump->setup(array('pathToMysqldump' => $path));
        $this->mysqldump->setExecutable($mysqldump);
        $this->mysqldump->backup($target, $appResult);
    }
}
