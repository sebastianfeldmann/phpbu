<?php
namespace phpbu\App\Backup\Cleaner\Stepwise\Keeper;

use PHPUnit\Framework\TestCase;

/**
 * OnePerGroup test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class OnePerGroupTest extends TestCase
{
    /**
     * Tests OnePerGroup::keep
     */
    public function testKeep()
    {
        $file1 = $this->createMock(\phpbu\App\Backup\File\Local::class);

        $file2 = $this->createMock(\phpbu\App\Backup\File\Local::class);

        $file3 = $this->createMock(\phpbu\App\Backup\File\Local::class);

        $file4 = $this->createMock(\phpbu\App\Backup\File\Local::class);

        $file1->method('getMTime')->willReturn(mktime(4, 10, 0, 3, 12, 2017));
        $file2->method('getMTime')->willReturn(mktime(5, 10, 0, 3, 12, 2017));
        $file3->method('getMTime')->willReturn(mktime(6, 10, 0, 3, 12, 2017));
        $file4->method('getMTime')->willReturn(mktime(6, 10, 0, 3, 13, 2017));

        $keeper = new OnePerGroup('Ymd');
        $this->assertTrue($keeper->keep($file1));
        $this->assertFalse($keeper->keep($file1));
        $this->assertFalse($keeper->keep($file3));
        $this->assertTrue($keeper->keep($file4));
    }
}
