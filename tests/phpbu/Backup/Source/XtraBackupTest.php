<?php
namespace phpbu\App\Backup\Source;

/**
 * XtraBackupTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XtraBackupTest extends \PHPUnit_Framework_TestCase
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
        $this->xtrabackup->setBinary('innobackupex');
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
        $this->xtrabackup->setup(array());
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::setUp
     *
     * @expectedException \RuntimeException
     */
    public function testSetUpCantFindBinary()
    {
        $xtra = new XtraBackup();
        $xtra->setup(array('pathToXtraBackup' => '/foo/bar'));
    }

    /**
     * Tests XtraBackup::setUp
     */
    public function testSetUpFindBinary()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $xtra = new XtraBackup();
        $xtra->setup(array('pathToXtraBackup' => $path));

        $this->assertTrue(true, 'no exception should be thrown');
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testShowStdErr()
    {
        $this->xtrabackup->setup(array('showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp \'./dump\' ' .
            '&& innobackupex --apply-log \'./dump\'' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testUser()
    {
        $this->xtrabackup->setup(array('user' => 'root'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp --user=\'root\' \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testPassword()
    {
        $this->xtrabackup->setup(array('password' => 'secret'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp --password=\'secret\' \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testHost()
    {
        $this->xtrabackup->setup(array('host' => 'example.com'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp --host=\'example.com\' \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testDatabases()
    {
        $this->xtrabackup->setup(array('databases' => 'db1,db2,db3.table1'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp --databases=\'db1 db2 db3.table1\' \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::getExec
     */
    public function testInclude()
    {
        $this->xtrabackup->setup(array('include' => '^mydatabase[.]mytable'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->xtrabackup->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'innobackupex --no-timestamp --include=\'^mydatabase[.]mytable\' \'./dump\' 2> /dev/null ' .
            '&& innobackupex --apply-log \'./dump\' 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests XtraBackup::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('execute')->willReturn($cliResult);

        $this->xtrabackup->setup(array());
        $this->xtrabackup->setExec($exec);
        $this->xtrabackup->backup($target, $appResult);
    }

    /**
     * Tests XtraBackup::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('execute')->willReturn($cliResult);

        $this->xtrabackup->setup(array());
        $this->xtrabackup->setExec($exec);
        $this->xtrabackup->backup($target, $appResult);
    }

    /**
     * Create Cli\Result mock.
     *
     * @param  integer $code
     * @return \phpbu\App\Backup\Cli\Result
     */
    protected function getCliResultMock($code)
    {
        $cliResult = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();

        $cliResult->method('getCmd')->willReturn('XtraBackup');
        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getOutput')->willReturn(array());
        $cliResult->method('wasSuccessful')->willReturn($code == 0);

        return $cliResult;
    }

    /**
     * Create Target mock.
     *
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock()
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        $target->method('getPath')->willReturn('.');
        $target->method('fileExists')->willReturn(false);
        $target->method('shouldBeCompressed')->willReturn(false);

        return $target;
    }
}
