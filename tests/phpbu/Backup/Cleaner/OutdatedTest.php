<?php
namespace phpbu\App\Backup\Cleaner;

/**
 * OutdatedTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class OutdatedTest extends TestCase
{
    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpNoOlder()
    {
        $cleaner = new Outdated();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Outdated();
        $cleaner->setup(['older' => 'false']);
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpAmountToLow()
    {
        $cleaner = new Outdated();
        $cleaner->setup(['older' => '0S']);
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpOlderToLow()
    {
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
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '3d'));

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
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(['older' => '5d']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Outdated::cleanup
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testCleanupNotWritable()
    {
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
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '3d'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
