<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class RsyncTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Rsync::setUp
     */
    public function testSetUpOk()
    {
        $rsync = new Rsync();
        $rsync->setup([
            'pathToRsync' => PHPBU_TEST_BIN,
            'path'        => 'foo',
            'user'        => 'dummy-user',
            'host'        => 'dummy-host'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Rsync::simulate
     */
    public function testSimulate()
    {
        $runner = $this->getRunnerMock();
        $runner->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'rsync'));

        $rsync = new Rsync($runner);
        $rsync->setup([
            'pathToRsync' => PHPBU_TEST_BIN,
            'path'        => 'foo',
            'user'        => 'dummy-user',
            'host'        => 'dummy-host'
        ]);

        $resultStub = $this->getAppResultMock();
        $resultStub->expects($this->once())
                   ->method('debug');
        $targetStub = $this->createTargetMock('/tmp/foo.bar');

        $rsync->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Rsync::setUp
     */
    public function testSetUpNoPath()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $rsync = new Rsync();
        $rsync->setup([
            'user' => 'dummy-user',
            'host' => 'dummy-host'
        ]);
    }

    /**
     * Tests Rsync::setUp
     */
    public function testSetUpNoPathOkWithRawArgs()
    {
        $rsync = new Rsync();
        $rsync->setup([
            'args' => 'dummy-args'
        ]);
        $this->assertTrue(true, 'there should not be an Exception');
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithCustomArgs()
    {
        $rsync  = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'args' => '--foo --bar']);

        $target = $this->createTargetMock('/foo/bar.txt');
        $exec   = $rsync->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/rsync --foo --bar', $exec->getCommand());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecMinimal()
    {
        $rsync  = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp']);

        $target = $this->createTargetMock('/foo/bar.txt');
        $exec   = $rsync->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/rsync -avz \'/foo/bar.txt\' \'/tmp\'', $exec->getCommand());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithPassword()
    {
        $password = 'secret';
        $env      = 'RSYNC_PASSWORD=' . escapeshellarg($password) . ' ';
        $rsync  = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp', 'password' => $password]);

        $target = $this->createTargetMock('/foo/bar.txt');
        $exec   = $rsync->getExecutable($target);

        $this->assertEquals($env . PHPBU_TEST_BIN . '/rsync -avz \'/foo/bar.txt\' \'/tmp\'', $exec->getCommand());
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithPasswordFile()
    {
        $file   = './.rsync-password';
        $rsync  = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp', 'passwordFile' => $file]);

        $target = $this->createTargetMock('/foo/bar.txt');
        $exec   = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -avz --password-file=' . escapeshellarg($file) . ' \'/foo/bar.txt\' \'/tmp\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithoutCompressionIfTargetIsCompressed()
    {
        $rsync  = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp']);
        $target = $this->createTargetMock('/foo/bar.txt', '/foo/bar.txt.gz');
        $exec   = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -av \'/foo/bar.txt.gz\' \'/tmp\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testGetExecWithExcludes()
    {
        $rsync = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp', 'exclude' => 'fiz:buz']);

        $target = $this->createTargetMock('/foo/bar.txt');
        $target->method('shouldBeCompressed')->willReturn(false);

        $exec = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -avz --exclude=\'fiz\' --exclude=\'buz\' \'/foo/bar.txt\' \'/tmp\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::sync
     */
    public function testSyncOk()
    {
        $target    = $this->createTargetMock('/tmp/foo.bar');
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $rsync = new Rsync();
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'path' => '/tmp', 'exclude' => 'fiz:buz']);
        $rsync->sync($target, $appResult);
    }

    /**
     * Tests Rsync::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $runner = $this->getRunnerMock();
        $runner->method('run')->willReturn($this->getRunnerResultMock(1, 'rsync'));

        $target    = $this->createTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->exactly(2))->method('debug');

        $rsync = new Rsync($runner);
        $rsync->setup(['pathToRsync' => PHPBU_TEST_BIN, 'args' => '-foo -bar']);
        $rsync->sync($target, $appResult);
    }
}
