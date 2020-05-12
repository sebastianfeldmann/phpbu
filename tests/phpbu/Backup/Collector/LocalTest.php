<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Target;
use PHPUnit\Framework\TestCase;

/**
 * Local test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class LocalTest extends TestCase
{
    /**
     * Test the Backup collector with no dynamic directory
     * Files not matching foo-%d.txt should be ignored.
     */
    public function testMatchFiles()
    {
        $path      = $this->getTestDataDir() . '/collector/static-dir';
        $filename  = 'foo-%d.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(4, $files, '4 files should be found');
    }

    /**
     * Test the Backup collector with no dynamic directory
     * Files not matching foo-%d.txt.zip should be ignored.
     */
    public function testMatchFilesCompressed()
    {
        $path      = $this->getTestDataDir() . '/collector/static-dir-compressed';
        $filename  = 'foo-%d.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(4, $files, '4 files should be found');
    }

    /**
     * Test the Backup collector with no dynamic directory
     * Files not matching foo-%d.txt.zip should be ignored.
     */
    public function testMatchFilesCustomCompressed()
    {
        $path      = $this->getTestDataDir() . '/collector/static-dir-custom';
        $filename  = 'foo-%d.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->appendFileSuffix('tar');
        $target->setCompression($this->getCompressionMockForCmd('tar', 'gz'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(3, $files, '3 files should be found');
    }

    /**
     * Test the Backup collector with one dynamic directory
     */
    public function testSingleDynamicDirectory()
    {
        $path      = $this->getTestDataDir() . '/collector/dynamic-dir/single/%m';
        $filename  = '%d.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(4, $files, '4 files should be found');
    }

    /**
     * Test the Backup collector with multi dynamic directories
     */
    public function testMultipleDynamicDirectories()
    {
        $path      = $this->getTestDataDir() . '/collector/dynamic-dir/multi/%m/%d';
        $filename  = '%H.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(8, $files, '8 files should be found');
    }

    /**
     * Test the Backup collector with dynamic directories in the beginning and static at the end
     */
    public function testMixedStaticEnd()
    {
        $path      = $this->getTestDataDir() . '/collector/mixed-dir/static-end/%m/foo';
        $filename  = 'dump.txt';
        $target    = new Target($path, $filename, strtotime('2014-03-02 22:30:57'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files, '2 files should be found');
    }

    /**
     * Tests Collector::getBackupFiles
     *
     * @issue #135
     */
    public function testThreeDynamicPlaceholderDirectories()
    {
        $path      = $this->getTestDataDir() . '/collector/dynamic-dir/issue-135/%y/%m/%d';
        $filename  = 'database-%Y%m%d-%H%i.sql';
        $target    = new Target($path, $filename, strtotime('2018-03-01 13:00:12'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(7, $files, '5 files should be found');
    }

    /**
     * Tests Local::getBackupFiles
     */
    public function testNotSkipsCurrentWhenTargetPathHasTrailingBackslash()
    {
        $path      = $this->getTestDataDir() . '/collector/static-dir/';
        $filename  = 'foo-%d.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $collector = new Local($target);
        $files     = $collector->getBackupFiles();

        $this->assertCount(4, $files, '4 files should be found');
    }

    /**
     * Create Compressor Mock.
     *
     * @param  string $cmd
     * @param  string $suffix
     * @return \phpbu\App\Backup\Target\Compression
     */
    protected function getCompressionMockForCmd($cmd, $suffix)
    {
        $compressorStub = $this->createMock(\phpbu\App\Backup\Target\Compression::class);
        $compressorStub->method('getCommand')->willReturn($cmd);
        $compressorStub->method('getSuffix')->willReturn($suffix);

        return $compressorStub;
    }

    /**
     * Return test data directory
     *
     * @return string
     */
    protected function getTestDataDir()
    {
        return realpath(__DIR__ . '/../../../_files');
    }
}
