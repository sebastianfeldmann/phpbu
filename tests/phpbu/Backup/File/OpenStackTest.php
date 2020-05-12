<?php
namespace phpbu\App\Backup\File;

use PHPUnit\Framework\TestCase;

/**
 * OpenStackTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class OpenStackTest extends TestCase
{
    public function testCreateFileWithCorrectProperties()
    {
        $storageObjectLastModified    = new \DateTimeImmutable('2018-05-08 14:14:54.0 +00:00');
        $storageObject                = $this->createMock(\OpenStack\ObjectStore\v1\Models\StorageObject::class);
        $storageObject->name          = 'path/dump.tar.gz';
        $storageObject->filename      = 'dump.tar.gz';
        $storageObject->contentLength = 102102;
        $storageObject->lastModified  = $storageObjectLastModified;
        $storageObject->expects($this->once())
                      ->method('delete');

        $container = $this->createMock(\OpenStack\ObjectStore\v1\Models\Container::class);
        $container->expects($this->once())
                  ->method('getObject')
                  ->with('path/dump.tar.gz')
                  ->willReturn($storageObject);

        $file = new OpenStack($container, $storageObject);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('path/dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
    }

    /**
     * Tests OpenStack::unlink
     */
    public function testOpenStackDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $storageObjectLastModified    = new \DateTimeImmutable('2018-05-08 14:14:54.0 +00:00');
        $storageObject                = $this->createMock(\OpenStack\ObjectStore\v1\Models\StorageObject::class);
        $storageObject->name          = 'path/dump.tar.gz';
        $storageObject->filename      = 'dump.tar.gz';
        $storageObject->contentLength = 102102;
        $storageObject->lastModified  = $storageObjectLastModified;
        $storageObject->expects($this->once())
                      ->method('delete')
                      ->will($this->throwException(new \Exception()));

        $container = $this->createMock(\OpenStack\ObjectStore\v1\Models\Container::class);
        $container->expects($this->once())
                  ->method('getObject')
                  ->with('path/dump.tar.gz')
                  ->willReturn($storageObject);

        $file = new OpenStack($container, $storageObject);
        $file->unlink();
    }
}
