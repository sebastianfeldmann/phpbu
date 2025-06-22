<?php

namespace phpbu\App\Backup\File;

use Exception;
use Google\Cloud\Storage\StorageObject;
use PHPUnit\Framework\TestCase;

/**
 * Google Drive file test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     David Dattée <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class GoogleCloudStorageTest extends TestCase
{
    /**
     * Test GoogleCloudStorage::unlink
     */
    public function testUnlink()
    {
        $file = $this->createMock(StorageObject::class);
        $file->expects($this->exactly(2))->method('name')->willReturn('dump.tar.gz');
        $file->expects($this->exactly(2))->method('info')->willReturn([
            'size'    => '102102',
            'updated' => '2024-09-05T10:22:24.539Z',
        ]);
        $file->expects($this->once())->method('delete');

        $file = new GoogleCloudStorage($file);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1725531744, $file->getMTime());

        $file->unlink();
    }

    /**
     * Tests GoogleDrive::unlink
     */
    public function testUnlinkFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $file = $this->createMock(StorageObject::class);
        $file->expects($this->exactly(2))->method('name')->willReturn('dump.tar.gz');
        $file->expects($this->exactly(2))->method('info')->willReturn([
            'size'    => '102102',
            'updated' => '2024-09-05T10:22:24.539Z',
        ]);
        $file
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new Exception());

        $file = new GoogleCloudStorage($file);
        $file->unlink();
    }
}
