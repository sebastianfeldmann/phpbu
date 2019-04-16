<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * SizeDiffPreviousPercentTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeDiffPreviousPercentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests SizeDiffPreviousPercent::pass
     */
    public function testPass()
    {
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($this->getFileListMock([100, 500, 1000, 1060]));
        $targetStub    = $this->createMock(Target::class);
        $targetStub->method('getSize')->willReturn(1060);

        $check = new SizeDiffPreviousPercent();

        $this->assertTrue(
            $check->pass($targetStub, '10', $collectorStub, $resultStub),
            'size of 1060 should be in range of 900 -  1100'
        );
    }

    /**
     * Tests SizeDiffPreviousPercent::pass
     */
    public function testFail()
    {
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($this->getFileListMock([100, 500, 1000, 1060]));
        $targetStub    = $this->createMock(Target::class);
        $targetStub->method('getSize')->willReturn(1060);

        $check = new SizeDiffPreviousPercent();

        $this->assertFalse(
            $check->pass($targetStub, '5', $collectorStub, $resultStub),
            'size of 1060 should not be in rage of 950 - 1050'
        );
    }

    /**
     * Tests SizeDiffPreviousPercent::simulate
     */
    public function testSimulate()
    {
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);
        $resultStub    = $this->createMock(Result::class);
        $resultStub->expects($this->once())->method('debug');

        $check = new SizeDiffPreviousPercent();
        $check->simulate($targetStub, '10', $collectorStub, $resultStub);
    }

    /**
     * Create a list of File stubs
     *
     * @param  array $sizes Size in byte the stubs will return on getSize()
     * @return \phpbu\App\Backup\File\Local[]
     */
    protected function getFileListMock(array $sizes)
    {
        $list = [];
        foreach ($sizes as $i => $size) {
            $fileStub = $this->createMock(\phpbu\App\Backup\File\Local::class);
            $fileStub->method('getSize')->willReturn($size);
            $list['201401' . str_pad($i + 1, 2, '0', STR_PAD_LEFT)] = $fileStub;
        }
        return $list;
    }
}
