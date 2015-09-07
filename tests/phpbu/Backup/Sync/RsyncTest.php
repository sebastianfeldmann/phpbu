<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\CliTest;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class RsyncTest extends CliTest
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
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithCustomArgs()
    {
        $target = $this->getTargetMock('/foo/bar.txt');
        $path   = $this->getBinDir();
        $rsync  = new Rsync();
        $rsync->setup(array('pathToRsync' => $path, 'args' => '--foo --bar'));
        $exec = $rsync->getExecutable($target);

        $this->assertEquals($path . '/rsync --foo --bar', $exec->getCommandLine());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecMinimal()
    {
        $target = $this->getTargetMock('/foo/bar.txt');
        $path   = $this->getBinDir();
        $rsync  = new Rsync();
        $rsync->setup(array('pathToRsync' => $path, 'path' => '/tmp'));
        $exec = $rsync->getExecutable($target);

        $this->assertEquals($path . '/rsync -avz \'/foo/bar.txt\' \'/tmp\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithoutCompressionIfTargetIsCompressed()
    {
        $target = $this->getTargetMock('/foo/bar.txt', '/foo/bar.txt.gz');
        $path   = $this->getBinDir();
        $rsync  = new Rsync();
        $rsync->setup(array('pathToRsync' => $path, 'path' => '/tmp'));
        $exec = $rsync->getExecutable($target);

        $this->assertEquals($path . '/rsync -av \'/foo/bar.txt.gz\' \'/tmp\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithExcludes()
    {
        $target = $this->getTargetMock('/foo/bar.txt');
        $target->method('shouldBeCompressed')->willReturn(false);

        $path  = $this->getBinDir();
        $rsync = new Rsync();
        $rsync->setup(array('pathToRsync' => $path, 'path' => '/tmp', 'exclude' => 'fiz:buz'));
        $exec = $rsync->getExecutable($target);

        $this->assertEquals($path . '/rsync -avz --exclude=\'fiz\' --exclude=\'buz\' \'/foo/bar.txt\' \'/tmp\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Rsync::sync
     */
    public function testSyncOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'rsync');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Rsync')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $rsync = new Rsync();
        $rsync->setExecutable($exec);
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
        $cliResult = $this->getCliResultMock(1, 'rsync');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Rsync')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->exactly(2))->method('debug');

        $rsync = new Rsync();
        $rsync->setExecutable($exec);
        $rsync->setup(array('args' => '-foo -bar'));
        $rsync->sync($target, $appResult);
    }
}
