<?php
namespace phpbu\App\Backup\Source;

/**
 * ArangodumpTest
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
class ArangodumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Arangodump
     *
     * @var \phpbu\App\Backup\Source\Arangodump
     */
    protected $arangodump;

    /**
     * Setup arangodump
     */
    public function setUp()
    {
        $this->arangodump = new Arangodump();
        $this->arangodump->setBinary('arangodump');
    }

    /**
     * Clear arangodump
     */
    public function tearDown()
    {
        $this->arangodump = null;
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testDefault()
    {
        $this->arangodump->setup(array());
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::setUp
     *
     * @expectedException \RuntimeException
     */
    public function testSetUpCantFindBinary()
    {
        $arangodump = new Arangodump();
        $arangodump->setup(array('pathToArangodump' => '/foo/bar'));
    }

    /**
     * Tests Arangodump::setUp
     */
    public function testSetUpFindBinary()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $arangodump = new Arangodump();
        $arangodump->setup(array('pathToArangodump' => $path));

        $this->assertTrue(true, 'no exception should be thrown');
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testShowStdErr()
    {
        $this->arangodump->setup(array('showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --output-directory \'./dump\'', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testUser()
    {
        $this->arangodump->setup(array('username' => 'root'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --server.username \'root\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testPassword()
    {
        $this->arangodump->setup(array('password' => 'secret'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --server.password \'secret\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testEndpoint()
    {
        $this->arangodump->setup(array('endpoint' => 'tcp://example.com:8529'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --server.endpoint \'tcp://example.com:8529\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testDatabase()
    {
        $this->arangodump->setup(array('database' => 'mydatabase'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --server.database \'mydatabase\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testCollections()
    {
        $this->arangodump->setup(array('collections' => 'collection1,collection2'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --collection \'collection1\' --collection \'collection2\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testDisableAuthentication()
    {
        $this->arangodump->setup(array('disableAuthentication' => 'true'));

        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --server.disable-authentication \'true\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testIncludeSystemCollections()
    {
        $this->arangodump->setup(array('includeSystemCollections' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --include-system-collections \'true\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::getExec
     */
    public function testDumpData()
    {
        $this->arangodump->setup(array('dumpData' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->arangodump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('arangodump --dump-data \'true\' --output-directory \'./dump\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Arangodump::backup
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

        $this->arangodump->setup(array());
        $this->arangodump->setExec($exec);
        $this->arangodump->backup($target, $appResult);
    }

    /**
     * Tests Arangodump::backup
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

        $this->arangodump->setup(array());
        $this->arangodump->setExec($exec);
        $this->arangodump->backup($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('arangodump');
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
