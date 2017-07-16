<?php
namespace phpbu\App\Backup\Check;

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
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector::class);
        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($this->getFileListMock([100, 500, 1000]));
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->method('getSize')->willReturn(1060);

        $check = new SizeDiffPreviousPercent();

        $this->assertTrue(
            $check->pass($targetStub, '10', $collectorStub, $resultStub),
            'size of stub should be about 900 -  1100'
        );
    }

    /**
     * Tests SizeDiffPreviousPercent::pass
     */
    public function testFail()
    {
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector::class);
        $collectorStub->expects($this->once())
                              ->method('getBackupFiles')
                              ->willReturn($this->getFileListMock([100, 500, 1000]));
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->method('getSize')->willReturn(1060);

        $check = new SizeDiffPreviousPercent();

        $this->assertFalse(
            $check->pass($targetStub, '5', $collectorStub, $resultStub),
            'size of stub should be about 900 -  1100'
        );
    }

    /**
     * Create a list of File stubs
     *
     * @param  array $sizes Size in byte the stubs will return on getSize()
     * @return \phpbu\App\Backup\File[]
     */
    protected function getFileListMock(array $sizes)
    {
        $list = [];
        foreach ($sizes as $i => $size) {
            $fileStub = $this->createMock(\phpbu\App\Backup\File::class);
            $fileStub->method('getSize')->willReturn($size);
            $list['201401' . str_pad($i + 1, 2, '0', STR_PAD_LEFT)] = $fileStub;
        }
        return $list;
    }
}
