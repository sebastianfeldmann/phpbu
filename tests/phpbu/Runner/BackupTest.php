<?php
namespace phpbu\App\Runner;

use phpbu\App\BaseMockery;
use phpbu\App\Result;
use phpbu\App\Runner\Mockery as RunnerMockery;
use PHPUnit\Framework\TestCase;

/**
 * Backup Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.1.0
 */
class BackupTest extends TestCase
{
    use BaseMockery;
    use RunnerMockery;

    /**
     * Tests Backup::run
     *
     * @throws \phpbu\App\Exception
     */
    public function testRunAllGood()
    {
        $source        = $this->createSourceMock($this->createStatusMock());
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     *
     * @throws \phpbu\App\Exception
     */
    public function __testRunAllGoodCompressFile()
    {
        $source        = $this->createSourceMock($this->createStatusMock('/tmp/foo'));
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunExclude()
    {
        $source        = $this->createSourceMock($this->createStatusMock());
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock(2);
        $configuration->method('isBackupActive')->will($this->onConsecutiveCalls(true, false));

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunCheckFail()
    {
        $source        = $this->createSourceMock($this->createStatusMock());
        $factory       = $this->createFactoryMock($source, false);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunCheckCrash()
    {
        $factory       = $this->createFactoryMockCheckCrash();
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunCryptCrash()
    {
        $factory       = $this->createFactoryMockCryptCrash();
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunSyncCrash()
    {
        $factory       = $this->createFactoryMockSyncCrash();
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunCleanerCrash()
    {
        $factory       = $this->createFactoryMockCleanerCrash();
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testRunStopOnFailure()
    {
        $factory       = $this->createFactoryMockStopOnFailure();
        $configuration = $this->createConfigurationMock(2);
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Backup($factory, $result);
        $runner->run($configuration);
    }
}
