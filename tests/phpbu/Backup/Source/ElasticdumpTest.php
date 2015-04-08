<?php
namespace phpbu\App\Backup\Source;

/**
 * ElasticdumpTest
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
class ElasticdumpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Elasticdump
     *
     * @var \phpbu\App\Backup\Source\Elasticdump
     */
    protected $elasticdump;

    /**
     * Setup elasticdump
     */
    public function setUp()
    {
        $this->elasticdump = new Elasticdump();
        $this->elasticdump->setBinary('elasticdump');
    }

    /**
     * Clear elasticdump
     */
    public function tearDown()
    {
        $this->elasticdump = null;
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testDefault()
    {
        $this->elasticdump->setup(array());
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://localhost:9200/\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::setUp
     *
     * @expectedException \RuntimeException
     */
    public function testSetUpCantFindBinary()
    {
        $elasticdump = new Elasticdump();
        $elasticdump->setup(array('pathToElasticdump' => '/foo/bar'));
    }

    /**
     * Tests Elasticdump::setUp
     */
    public function testSetUpFindBinary()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $elasticdump = new Elasticdump();
        $elasticdump->setup(array('pathToElasticdump' => $path));

        $this->assertTrue(true, 'no exception should be thrown');
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testShowStdErr()
    {
        $this->elasticdump->setup(array('showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://localhost:9200/\' --output=\'/backups/elasticsearch/backup.json\'', $cmd);
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testUser()
    {
        $this->elasticdump->setup(array('user' => 'root'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://root@localhost:9200/\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testUserPassword()
    {
        $this->elasticdump->setup(array('user' => 'root', 'password' => 'secret'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://root:secret@localhost:9200/\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testHost()
    {
        $this->elasticdump->setup(array('host' => 'example.com'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://example.com/\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testIndex()
    {
        $this->elasticdump->setup(array('index' => 'myindex'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://localhost:9200/myindex\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::getExec
     */
    public function testType()
    {
        $this->elasticdump->setup(array('type' => 'mapping'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->elasticdump->getExec($this->getTargetMock());
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('elasticdump --input=\'http://localhost:9200/\' --type=\'mapping\' --output=\'/backups/elasticsearch/backup.json\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Elasticdump::backup
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

        $this->elasticdump->setup(array());
        $this->elasticdump->setExec($exec);
        $this->elasticdump->backup($target, $appResult);
    }

    /**
     * Tests Elasticdump::backup
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

        $this->elasticdump->setup(array());
        $this->elasticdump->setExec($exec);
        $this->elasticdump->backup($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('elasticdump');
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
        $target->method('getPathnamePlain')->willReturn('/backups/elasticsearch/backup.json');
        $target->method('fileExists')->willReturn(false);
        $target->method('shouldBeCompressed')->willReturn(false);

        return $target;
    }
}
