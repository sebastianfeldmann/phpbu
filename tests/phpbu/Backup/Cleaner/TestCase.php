<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Util\Str;

/**
 * TestCase
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Test execution time
     *
     * @var integer
     */
    protected $time;

    /**
     * Create a list of File stubs.
     *
     * @param  array $files List of files to mock
     * @return array<splFileInfo>
     */
    protected function getFileMockList(array $files)
    {
        $list = [];
        foreach ($files as $i => $file) {
            $index        = isset($file['mTime'])
                          ? date('YmdHis', $file['mTime'])
                          : '201401' . str_pad($i, 2, '0', STR_PAD_LEFT) . '0000';
            $list[$index] = $this->getFileMock(
                isset($file['size'])            ? $file['size']            : null,
                isset($file['shouldBeDeleted']) ? $file['shouldBeDeleted'] : null,
                isset($file['mTime'])           ? $file['mTime']           : null,
                isset($file['writable'])        ? $file['writable']        : true
            );
        }
        return $list;
    }

    /**
     * Create a list of File stubs.
     *
     * @param  integer $size            Size in byte the stubs will return on getSize()
     * @param  boolean $shouldBeDeleted Should this file be deleted after cleanup
     * @param  integer $mTime           Last modification date the stub will return on getMTime()
     * @param  boolean $writable        Is the file writable
     * @return array<splFileInfo>
     */
    protected function getFileMock($size, $shouldBeDeleted, $mTime, $writable)
    {
        /* @var $fileStub \PHPUnit\Framework\MockObject */
        $fileStub = $this->createMock(\phpbu\App\Backup\File::class);
        $fileStub->method('getMTime')->willReturn($mTime);
        $fileStub->method('getSize')->willReturn($size);
        $fileStub->method('isWritable')->willReturn($writable);
        if ($shouldBeDeleted) {
            $fileStub->expects($this->once())
                     ->method('unlink');
        }

        return $fileStub;
    }

    /**
     * Get a fake last modified date.
     *
     * @param  string $offset
     * @return integer
     */
    protected function getMTime($offset)
    {
        return $this->getTime() - Str::toTime($offset);
    }

    /**
     * Return the current time.
     *
     * @return integer
     */
    protected function getTime()
    {
        if (null == $this->time) {
            $this->time = time();
        }
        return $this->time;
    }
}
