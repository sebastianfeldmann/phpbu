<?php
namespace phpbu\App\Backup\Sync;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class RsyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Rsync::setUp
     */
    public function testSetUpOk()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => 'foo',
            'user' => 'dummy-user',
            'host' => 'dummy-host'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Rsync::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'user' => 'dummy-user',
            'host' => 'dummy-host'
        ));
    }

    /**
     * Tests Rsync::setUp
     */
    public function testSetUpNoPathOkWithRawArgs()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'args' => 'dummy-args'
        ));
        $this->assertTrue(true, 'there should not be an Exception');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringEmptyWithoutSetup()
    {
        $rsync = new Rsync();
        $this->assertEquals('', $rsync->getRsyncHostString(), 'should be empty on init');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringHostOnly()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'host' => 'example.com'
        ));
        $this->assertEquals('example.com:', $rsync->getRsyncHostString(), 'should be \'host:\'');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringHostAndUser()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'user' => 'user.name',
            'host' => 'example.com'
        ));
        $this->assertEquals('user.name@example.com:', $rsync->getRsyncHostString(), 'should have \'user@host:\'');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringEmptyOnUserOnly()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'user' => 'user.name'
        ));
        $this->assertEquals('', $rsync->getRsyncHostString(), 'should still be empty');
    }

    /**
     * Tests Rsync::getExec
     */
    public function testGetExecWithCustomArgs()
    {
        $target = $this->getTargetMock();
        $rsync  = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setup(array('args' => '--foo --bar'));
        $exec = $rsync->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('rsync --foo --bar', $call);
    }

    /**
     * Tests Rsync::getExec
     */
    public function testGetExecMinimal()
    {
        $target = $this->getTargetMock();
        $rsync  = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setup(array('path' => '/tmp'));
        $exec = $rsync->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('rsync -avz \'/foo/bar.txt\' \'/tmp\' 2> /dev/null', $call);
    }

    /**
     * Tests Rsync::getExec
     */
    public function testGetExecWithoutCompressionIfTargetIsCompressed()
    {
        $target = $this->getTargetMock('/foo/bar.txt', true);
        $rsync  = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setup(array('path' => '/tmp'));
        $exec = $rsync->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('rsync -av \'/foo/bar.txt\' \'/tmp\' 2> /dev/null', $call);
    }

    /**
     * Tests Rsync::getExec
     */
    public function testGetExecDirSyncAndDelete()
    {
        $target = $this->getTargetMock();
        $rsync  = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setup(array('path' => '/tmp', 'dirsync' => 'true', 'delete' => 'true'));
        $exec = $rsync->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('rsync -avz --delete \'/foo\' \'/tmp\' 2> /dev/null', $call);
    }

    /**
     * Tests Rsync::getExec
     */
    public function testGetExecWithExcludes()
    {
        $target = $this->getTargetMock();
        $rsync  = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setup(array('path' => '/tmp', 'exclude' => 'fiz:buz'));
        $exec = $rsync->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('rsync -avz --exclude=\'fiz\' --exclude=\'buz\' \'/foo/bar.txt\' \'/tmp\' 2> /dev/null', $call);
    }

    /**
     * Tests Rsync::sync
     */
    public function testSyncOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('execute')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $rsync = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setExec($exec);
        $rsync->sync($target, $appResult);
    }

    /**
     * Tests Rsync::sync
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSyncFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('execute')->willReturn($cliResult);
        $appResult->expects($this->exactly(2))->method('debug');

        $rsync = new Rsync();
        $rsync->setBinary('rsync');
        $rsync->setExec($exec);
        $rsync->setup(array('args' => '-foo -bar'));
        $rsync->sync($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('rsync');
        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getOutput')->willReturn(array());
        $cliResult->method('wasSuccessful')->willReturn($code == 0);

        return $cliResult;
    }

    /**
     * Create Target mock.
     *
     * @param  string  $pathname
     * @param  boolean $compressed
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock($pathname = '/foo/bar.txt', $compressed = false)
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        $target->method('getPath')->willReturn(dirname($pathname));
        $target->method('getPathname')->willReturn($pathname);
        $target->method('fileExists')->willReturn(false);
        $target->method('shouldBeCompressed')->willReturn($compressed);

        return $target;
    }
}
