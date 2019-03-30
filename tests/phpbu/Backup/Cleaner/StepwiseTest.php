<?php
namespace phpbu\App\Backup\Cleaner;

/**
 * StepwiseTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class StepwiseTest extends TestCase
{
    /**
     * Tests Stepwise::cleanup
     */
    public function testCleanupDeleteOldestFile()
    {
        $fileList      = $this->getFileMockList(
            [
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('8h'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('12h'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('1w'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2w'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2m'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3m'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('12m'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('13m'),
                ],
                [
                    'size'            => 100,
                    'shouldBeDeleted' => true,
                    'mTime'           => $this->getMTime('20m'),
                ],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Stepwise();
        $cleaner->setup(
            [
                'daysToKeepAll'      => '1',
                'daysToKeepDaily'    => '3',
                'weeksToKeepWeekly'  => '3',
                'monthToKeepMonthly' => '3',
                'yearsToKeepYearly'  => '1',
            ]
        );

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Stepwise::cleanup
     */
    public function testCleanupInvalidRange()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $fileList      = $this->getFileMockList(
            [
                [
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => -5,
                ]
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Stepwise();
        $cleaner->setup(
            [
                'daysToKeepAll'      => '1',
                'daysToKeepDaily'    => '3',
                'weeksToKeepWeekly'  => '3',
                'monthToKeepMonthly' => '3',
                'yearsToKeepYearly'  => '1',
            ]
        );

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
