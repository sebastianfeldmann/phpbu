<?php
namespace phpbu\App\Backup\Source;

/**
 * MysqldumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MysqldumpTest extends \PHPUnit_Framework_TestCase
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
        $this->mysqldump->setBinary('mysqldump');
    }

    /**
     * Clear mysqldump
     */
    public function tearDown()
    {
        $this->mysqldump = null;
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testDefault()
    {
        $this->mysqldump->setup(array());
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testShowStdErr()
    {
        $this->mysqldump->setup(array('showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --all-databases', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testUser()
    {
        $this->mysqldump->setup(array('user' => 'root'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --user=\'root\' --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testPassword()
    {
        $this->mysqldump->setup(array('password' => 'secret'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --password=\'secret\' --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testHost()
    {
        $this->mysqldump->setup(array('host' => 'example.com'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --host=\'example.com\' --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testDatabases()
    {
        $this->mysqldump->setup(array('databases' => 'db1,db2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --databases \'db1\' \'db2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testTables()
    {
        $this->mysqldump->setup(array('tables' => 'db1.table1,db2.table2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --tables \'db1.table1\' \'db2.table2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testTablesOverDatabases()
    {
        $this->mysqldump->setup(array(
            'tables'    => 'db1.table1,db2.table2',
            'databases' => 'db1,db2',
        ));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --tables \'db1.table1\' \'db2.table2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testNoData()
    {
        $this->mysqldump->setup(array('noData' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --all-databases --no-data 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testQuick()
    {
        $this->mysqldump->setup(array('quick' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump -q --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testCompress()
    {
        $this->mysqldump->setup(array('compress' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump -C --all-databases 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testIgnoreTables()
    {
        $this->mysqldump->setup(array('ignoreTables' => 'db.table1,db.table2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mysqldump --all-databases --ignore-table=\'db.table1\' --ignore-table=\'db.table2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mysqldump::getExec
     */
    public function testStructureOnly()
    {
        $this->mysqldump->setup(array('structureOnly' => 'db.table1,db.table2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mysqldump->getExec();
        $cmd  = (string) $exec->getExec();

        $this->assertEquals(
            '(' .
            'mysqldump --all-databases --no-data 2> /dev/null ' .
            '&& mysqldump --all-databases --no-data ' .
            '--ignore-table=\'db.table1\' --ignore-table=\'db.table2\' ' .
            '--skip-add-drop-table --no-create-db --no-create-info 2> /dev/null' .
            ')',
            $cmd
        );
    }

    /**
     * Tests Mysqldump::backup
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

        $this->mysqldump->setup(array());
        $this->mysqldump->setExec($exec);
        $this->mysqldump->backup($target, $appResult);
    }

    /**
     * Tests Mysqldump::backup
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

        $this->mysqldump->setup(array());
        $this->mysqldump->setExec($exec);
        $this->mysqldump->backup($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('mysqldump');
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
