<?php
namespace phpbu\Backup;

/**
 * Collector test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Backup collector with no dynamic directory
     * Files not matching foo-%d.txt should be ignored.
     */
    public function testMatchFiles()
    {
        $dirname   = $this->getTestDataDir() . '/collector/static-dir';
        $filename  = 'foo-%d.txt';
        $target    = new Target($dirname, $filename);
        $collector = new Collector($target);
        $files     = $collector->getBackupFiles();

        $this->assertEquals(4, count($files), '4 files should be found');
    }

    /**
     * Test the Backup collector with one dynamic directory
     */
    public function testSingleDynamicDirectory()
    {
        $dirname   = $this->getTestDataDir() . '/collector/dynamic-dir/single/%m';
        $filename  = '%H.txt';
        $target    = new Target($dirname, $filename);
        $collector = new Collector($target);
        $files     = $collector->getBackupFiles();

        $this->assertEquals(4, count($files), '4 files should be found');
    }

    /**
     * Test the Backup collector with two dynamic directories
     */
    public function testMultipleDynamicDirectories()
    {
        $dirname   = $this->getTestDataDir() . '/collector/dynamic-dir/multi/%m/%d';
        $filename  = '%H.txt';
        $target    = new Target($dirname, $filename);
        $collector = new Collector($target);
        $files     = $collector->getBackupFiles();

        $this->assertEquals(8, count($files), '8 files should be found');
    }

    /**
     * Return testdata directory
     *
     * @return string
     */
    protected function getTestDataDir()
    {
        return realpath(__DIR__ . '/../../_files');
    }
}