<?php
namespace phpbu\App\Backup\Source;

/**
 * MongodumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class MongodumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mongodump
     *
     * @var \phpbu\App\Backup\Source\Mongodump
     */
    protected $mongodump;

    /**
     * Setup Mongodump
     */
    public function setUp()
    {
        $this->mongodump = new Mongodump();
        $this->mongodump->setBinary('mongodump');
    }

    /**
     * Clear Mongodump
     */
    public function tearDown()
    {
        $this->mongodump = null;
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testDefault()
    {
        $this->mongodump->setup(array());
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testShowStdErr()
    {
        $this->mongodump->setup(array('showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\'', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testUser()
    {
        $this->mongodump->setup(array('user' => 'root'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --user \'root\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testPassword()
    {
        $this->mongodump->setup(array('password' => 'secret'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --password \'secret\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testHost()
    {
        $this->mongodump->setup(array('host' => 'example.com'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --host \'example.com\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testDatabases()
    {
        $this->mongodump->setup(array('databases' => 'db1,db2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --database \'db1\' --database \'db2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testCollections()
    {
        $this->mongodump->setup(array('collections' => 'collection1,collection2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --collection \'collection1\' --collection \'collection2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testIPv6()
    {
        $this->mongodump->setup(array('ipv6' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --ipv6 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testExcludeCollections()
    {
        $this->mongodump->setup(array('excludeCollections' => 'col1,col2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --excludeCollection \'col1\' \'col2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::getExec
     */
    public function testExcludeCollectionsWithPrefix()
    {
        $this->mongodump->setup(array('excludeCollectionsWithPrefix' => 'pre1,pre2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->mongodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('mongodump --out \'./dump\' --excludeCollectionWithPrefix \'pre1\' \'pre2\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Mongodump::backup
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

        $this->mongodump->setup(array());
        $this->mongodump->setExec($exec);
        $this->mongodump->backup($target, $appResult);
    }

    /**
     * Tests Mongodump::backup
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

        $this->mongodump->setup(array());
        $this->mongodump->setExec($exec);
        $this->mongodump->backup($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('mongodump');
        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getOutput')->willReturn(array());
        $cliResult->method('wasSuccessful')->willReturn($code == 0);

        return $cliResult;
    }

    /**
     * Create Target mock.
     *
     * @param  string $path
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock($path = '.')
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        $target->method('getPath')->willReturn($path);
        $target->method('fileExists')->willReturn(false);
        $target->method('shouldBeCompressed')->willReturn(false);

        return $target;
    }
}
