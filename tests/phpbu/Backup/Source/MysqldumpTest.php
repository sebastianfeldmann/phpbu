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
     * Tests Mysqldump::getExecutable
     */
    public function testDefault()
    {
        $target    = $this->getTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump:setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupFail()
    {
        $mysqldump = new Mysqldump();
        $mysqldump->setup(
            [
                'pathToMysqldump' => PHPBU_TEST_BIN,
                'databases'       => 'foo',
                'filePerTable'    => 'true',
                'structureOnly'   => 'foo,bar,baz'
            ]
        );
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testPipeCompression()
    {
        $target = $this->getTargetMock('/tmp/foo.sql', '/tmp/foo.sql.gz');
        $target->method('getCompression')->willReturn($this->getCompressionMock('gzip', 'gz'));

        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --all-databases | ' . PHPBU_TEST_BIN . '/gzip > /tmp/foo.sql.gz',
            $executable->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testLockTables()
    {
        $target    = $this->getTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'lockTables' => 'true']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --lock-tables --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testFilePerTable()
    {
        $target    = $this->getTargetMock('/tmp/foo');
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'filePerTable' => 'true']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --all-databases --tab=\'/tmp/foo.dump\'',
            $executable->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testHexBlob()
    {
        $target    = $this->getTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'hexBlob' => 'true']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --hex-blob --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testExtendedInsert()
    {
        $target    = $this->getTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'extendedInsert' => 'true']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump -e --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'mysqldump'));

        $target    = $this->getTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mysqldump = new Mysqldump($runner);
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $status = $mysqldump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mysqldump::backup
     */
    public function testSimulate()
    {
        $runner    = $this->getRunnerMock();
        $target    = $this->getTargetMock();
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mysqldump = new Mysqldump($runner);
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $status = $mysqldump->simulate($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mysqldump::backup
     */
    public function testBackupOkCompressed()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'mysqldump'));

        $target = $this->getTargetMock('/tmp/foo.sql', '/tmp/foo.sql.gz');
        $target->method('getCompression')->willReturn($this->getCompressionMock('gzip', 'gz'));


        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mysqldump= new Mysqldump($runner);
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $status = $mysqldump->backup($target, $appResult);

        $this->assertTrue($status->handledCompression());
    }

    /**
     * Tests Mysqldump::backup
     */
    public function testBackupFilePerTable()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'mysqldump'));


        $targetDir = sys_get_temp_dir() . '/foo';
        $target    = $this->getTargetMock($targetDir);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mysqldump= new Mysqldump($runner);
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'filePerTable' => 'true']);

        $status = $mysqldump->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
        rmdir($targetDir . '.dump');
    }

    /**
     * Tests Mysqldump::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $file = sys_get_temp_dir() . '/fakedump';
        file_put_contents($file, '# mysql fake dump');

        $runnerResultMock = $this->getRunnerResultMock(1, 'mysqldump', '', '', $file);
        $runner           = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($runnerResultMock);

        $target    = $this->getTargetMock($file);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $mysqldump= new Mysqldump($runner);
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        try {
            $mysqldump->backup($target, $appResult);
        } catch (\Exception $e) {
            $this->assertFalse(file_exists($file));
            throw $e;
        }
    }
}
