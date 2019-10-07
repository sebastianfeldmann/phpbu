<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\Backup\Restore\Plan;
use phpbu\App\BaseMockery;

/**
 * MysqldumpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class MysqldumpTest extends \PHPUnit\Framework\TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testDefault()
    {
        $target    = $this->createTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump:setup
     */
    public function testSetupFail()
    {
        $this->expectException('phpbu\App\Exception');
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
        $target = $this->createTargetMock('/tmp/foo.sql', '/tmp/foo.sql.gz');
        $target->method('getCompression')->willReturn($this->createCompressionMock('gzip', 'gz'));

        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN]);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --all-databases | '
            . PHPBU_TEST_BIN . '/gzip > /tmp/foo.sql.gz',
            $executable->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testLockTables()
    {
        $target    = $this->createTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'lockTables' => 'true']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --lock-tables --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testPort()
    {
        $target    = $this->createTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'port' => '4711']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --port=\'4711\' --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testProtocol()
    {
        $target    = $this->createTargetMock();
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'protocol' => 'TCP']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/mysqldump --protocol=\'TCP\' --all-databases', $executable->getCommand());
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testFilePerTable()
    {
        $target    = $this->createTargetMock('/tmp/foo');
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
    public function testGtidPurged()
    {
        $target    = $this->createTargetMock('/tmp/foo');
        $mysqldump = new Mysqldump();
        $mysqldump->setup(['pathToMysqldump' => PHPBU_TEST_BIN, 'gtidPurged' => 'AUTO']);

        $executable = $mysqldump->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/mysqldump --set-gtid-purged=\'AUTO\' --all-databases > /tmp/foo',
            $executable->getCommand()
        );
    }

    /**
     * Tests Mysqldump::getExecutable
     */
    public function testHexBlob()
    {
        $target    = $this->createTargetMock();
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
        $target    = $this->createTargetMock();
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

        $target    = $this->createTargetMock();
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
        $target    = $this->createTargetMock();
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

        $target = $this->createTargetMock('/tmp/foo.sql', '/tmp/foo.sql.gz');
        $target->method('getCompression')->willReturn($this->createCompressionMock('gzip', 'gz'));


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
        $target    = $this->createTargetMock($targetDir);
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
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $file = sys_get_temp_dir() . '/fakedump';
        file_put_contents($file, '# mysql fake dump');

        $runnerResultMock = $this->getRunnerResultMock(1, 'mysqldump', '', '', $file);
        $runner           = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($runnerResultMock);

        $target    = $this->createTargetMock($file);
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

    /**
     * Tests Mysqldump::restore
     */
    public function testRestorePasswordMasked()
    {
        $targetFile = '/tmp/backup/dump.sql';
        $target     = $this->createTargetMock($targetFile);

        $plan        = new Plan();
        $planRestore = [
            'mysql  --passsword=******  < ' . $targetFile,
        ];

        $configuration = [
            'pathToMysqldump' => PHPBU_TEST_BIN,
            'password'        => 'password',
        ];
        $mysqldump     = new Mysqldump();
        $mysqldump->setup($configuration);

        $status = $mysqldump->restore($target, $plan);

        $this->assertEquals($planRestore, $plan->getRestoreCommands());
        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Mysqldump::restore
     */
    public function testRestoreFilePerTable()
    {
        $targetFile = '/tmp/backup/dump.sql';
        $target     = $this->createTargetMock($targetFile);

        $plan        = new Plan();
        $planRestore = [
            'tar -xvf ' . $targetFile . '.tar',
            'find ' . $targetFile . '.dump -name "*.sql" -exec sh -c \' mysql databaseToBackup   < "$0" \' {} ";"',
            'find ' . $targetFile . '.dump -name "*.txt" -exec sh -c \' mysqlimport databaseToBackup  "$0" \' {} ";"',
        ];

        $configuration = [
            'pathToMysqldump' => PHPBU_TEST_BIN,
            'filePerTable'    => 'true',
            'databases'       => 'databaseToBackup',
        ];
        $mysqldump     = new Mysqldump();
        $mysqldump->setup($configuration);

        $status = $mysqldump->restore($target, $plan);

        $this->assertEquals($planRestore, $plan->getRestoreCommands());
        $this->assertFalse($status->handledCompression());
    }
}
