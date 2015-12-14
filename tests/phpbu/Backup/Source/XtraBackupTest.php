<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;
use phpbu\App\Util\Cli;

/**
 * XtraBackup Source Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XtraBackupTest extends CliTest
{
    /**
     * XtraBackup
     *
     * @var \phpbu\App\Backup\Source\XtraBackup
     */
    protected $xtrabackup;

    /**
     * Setup XtraBackup
     */
    public function setUp()
    {
        $this->xtrabackup = new XtraBackup();
    }

    /**
     * Clear XtraBackup
     */
    public function tearDown()
    {
        $this->xtrabackup = null;
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testDefault()
    {
        $expectedDump  = 'innobackupex --no-timestamp \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $target        = $this->getTargetMock('./foo.dump');
        $path          = $this->getBinDir();
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $this->xtrabackup->setup(array('pathToXtraBackup' => $path));

        $executable = $this->xtrabackup->getExecutable($target);

        $this->assertEquals($expected, $executable->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testDataDir()
    {
        $expectedDump  = 'innobackupex --no-timestamp --datadir=\'/x/mysql\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $target        = $this->getTargetMock('./foo.dump');
        $path          = $this->getBinDir();
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $this->xtrabackup->setup(['pathToXtraBackup' => $path, 'dataDir' => '/x/mysql']);

        $executable = $this->xtrabackup->getExecutable($target);

        $this->assertEquals($expected, $executable->getCommandLine());
    }

    /**
     * Tests XtraBackup::getExecutable
     */
    public function testDatabases()
    {
        $expectedDump  = 'innobackupex --no-timestamp --databases=\'db1 db2 db3.table1\' \'./dump\' 2> /dev/null';
        $expectedApply = 'innobackupex --apply-log \'./dump\' 2> /dev/null';
        $target        = $this->getTargetMock('./foo.dump');
        $path          = $this->getBinDir();
        $expected      = '(' . $path . '/' . $expectedDump . ' && ' . $path . '/' . $expectedApply . ')';
        $this->xtrabackup->setup(array('pathToXtraBackup' => $path, 'databases' => 'db1,db2,db3.table1'));

        $executable = $this->xtrabackup->getExecutable($target);

        $this->assertEquals($expected, $executable->getCommandLine());
    }

    /**
     * Tests XtraBackup::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'innobackupex');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Innobackupex')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->xtrabackup->setup(array());
        $this->xtrabackup->setExecutable($exec);
        $status = $this->xtrabackup->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests XtraBackup::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'innobackupex');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Innobackupex')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('run')->willReturn($cliResult);

        $this->xtrabackup->setup(array());
        $this->xtrabackup->setExecutable($exec);
        $this->xtrabackup->backup($target, $appResult);
    }
}
