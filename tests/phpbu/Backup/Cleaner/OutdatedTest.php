<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * OutdatedTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class OutdatedTest extends TestCase
{
    /**
     * Tests Outdated::setUp
     */
    public function testSetUpNoOlder()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Outdated();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Outdated::setUp
     */
    public function testSetUpInvalidValue()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Outdated();
        $cleaner->setup(['older' => 'false']);
    }

    /**
     * Tests Outdated::setUp
     */
    public function testSetUpAmountToLow()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Outdated();
        $cleaner->setup(['older' => '0S']);
    }

    /**
     * Tests Outdated::setUp
     */
    public function testSetUpOlderToLow()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Outdated();
        $cleaner->setup(['older' => '0S']);
    }

    /**
     * Tests Outdated::cleanup
     */
    public function testCleanupDeleteFiles()
    {
        $fileList      = $this->getFileMockList(
            [
                [
                    'size'            => 100,
                    'shouldBeDeleted' => true,
                    'mTime'           => $this->getMTime('4d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('1d'),
                ],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(['older' => '3d']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Outdated::cleanup
     */
    public function testCleanupDeleteNoFile()
    {
        $fileList      = $this->getFileMockList(
            [
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('4d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('1d'),
                ],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

        $collectorStub->expects($this->once())->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(['older' => '5d']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Outdated::cleanup
     */
    public function testCleanupNotWritable()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $fileList      = $this->getFileMockList(
            [
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('4d'),
                    'writable'        => false,
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(['older' => '3d']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
