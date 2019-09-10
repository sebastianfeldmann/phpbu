<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * StepwiseTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
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
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('1d')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('2d')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('3d')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('1w')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('2w')],
                ['size' => 100, 'shouldBeDeleted' => true,  'mTime' => $this->getMTime('2y')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('2i')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('3i')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('5i')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('6i')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => $this->getMTime('7i')],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

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
    public function testCleanupDeleteOldestFileIssue197()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:59:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:58:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:55:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:54:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:53:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 19:48:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-13 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-12 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-11 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-10 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-09 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-08 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-07 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-06 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-05 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-04 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-03 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-02 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-08-01 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-31 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-30 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-29 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-28 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-27 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-26 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-25 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-24 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-22 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-07-06 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-06-27 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => false, 'mTime' => strtotime('2019-05-10 03:00:00')],
                ['size' => 100, 'shouldBeDeleted' => true,  'mTime' => strtotime('2019-05-09 03:00:00')],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Stepwise(strtotime('2019-08-13 20:00:00'));
        $cleaner->setup(
            [
                'daysToKeepAll'      => '10',
                'daysToKeepDaily'    => '10',
                'weeksToKeepWeekly'  => '1',
                'monthToKeepMonthly' => '1',
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
        $resultStub    = $this->createMock(Result::class);
        $collectorStub = $this->createMock(Local::class);
        $targetStub    = $this->createMock(Target::class);

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
