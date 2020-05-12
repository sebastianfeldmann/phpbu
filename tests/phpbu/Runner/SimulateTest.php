<?php
namespace phpbu\App\Runner;

use phpbu\App\BaseMockery;
use phpbu\App\Result;
use phpbu\App\Runner\Mockery as RunnerMockery;
use PHPUnit\Framework\TestCase;

/**
 * Simulate Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.1.0
 */
class SimulateTest extends TestCase
{
    use BaseMockery;
    use RunnerMockery;

    /**
     * Tests Backup::run
     *
     * @throws \Exception
     */
    public function testUncompressed()
    {
        $source        = $this->createSourceMock($this->createStatusMock());
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Simulate($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testCompressFile()
    {
        $source        = $this->createSourceMock($this->createStatusMock('/tmp/foo'));
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Simulate($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testCompressDirectory()
    {
        $source        = $this->createSourceMock($this->createStatusMock('/tmp/foo', true));
        $factory       = $this->createFactoryMock($source, true);
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Simulate($factory, $result);
        $runner->run($configuration);
    }

    /**
     * Tests Backup::run
     */
    public function testCompressDirectoryExtraCompress()
    {
        $source        = $this->createSourceMock($this->createStatusMock('/tmp/foo', true));
        $factory       = $this->createFactoryMock(
            $source,
            true,
            1,
            $this->createTargetMock('foo', 'foo.tar.gz')
        );
        $configuration = $this->createConfigurationMock();
        $configuration->method('isBackupActive')->willReturn(true);

        $result = new Result();
        $runner = new Simulate($factory, $result);
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
        $runner = new Simulate($factory, $result);
        $runner->run($configuration);
    }
}
