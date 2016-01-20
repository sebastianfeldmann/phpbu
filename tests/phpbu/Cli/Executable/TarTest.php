<?php
namespace phpbu\App\Cli\Executable;

/**
 * Tar Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class TarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Tar::getCommandLine
     */
    public function testDefault()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = sys_get_temp_dir();
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir)->archiveTo('/tmp/foo.tar');

        $this->assertEquals($path . '/tar -cf \'/tmp/foo.tar\' -C \'' . $dir .  '\' \'.\'', $tar->getCommandLine());
    }

    /**
     * Tests Tar::getCommandLine
     */
    public function testCompressionGzip()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = sys_get_temp_dir();
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir)->archiveTo('/tmp/foo.tar.gz')->useCompression('gzip');

        $this->assertEquals($path . '/tar -zcf \'/tmp/foo.tar.gz\' -C \'' . $dir .  '\' \'.\'', $tar->getCommandLine());
    }

    /**
     * Tests Tar::getCommandLine
     */
    public function testCompressionBzip2()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = sys_get_temp_dir();
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir)->archiveTo('/tmp/foo.tar.bzip2')->useCompression('bzip2');

        $this->assertEquals($path . '/tar -jcf \'/tmp/foo.tar.bzip2\' -C \'' . $dir .  '\' \'.\'', $tar->getCommandLine());
    }

    /**
     * Tests Tar::getCommandLine
     */
    public function testRemoveSourceDir()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = sys_get_temp_dir();
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir)->archiveTo('/tmp/foo.tar')->removeSourceDirectory(true);

        $this->assertEquals(
            '(' . $path . '/tar -cf \'/tmp/foo.tar\' -C \'' . $dir .  '\' \'.\''
          . ' && rm -rf \'' . $dir . '\')',
            $tar->getCommandLine()
        );
    }

    /**
     * Tests Tar::archiveDirectory
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSourceNoDirectory()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = __DIR__ . '/foo';
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir);
    }

    /**
     * Tests Tar::getProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoSource()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $tar  = new Tar($path);
        $tar->run();
    }

    /**
     * Tests Tar::getProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoTarget()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $dir  = __DIR__;
        $tar  = new Tar($path);
        $tar->archiveDirectory($dir);
        $tar->run();
    }

    /**
     * Tests Tar::isCompressorValid
     */
    public function testIsCompressorValid()
    {
        $this->assertTrue(Tar::isCompressorValid('gzip'));
        $this->assertTrue(Tar::isCompressorValid('bzip2'));
        $this->assertFalse(Tar::isCompressorValid('zip'));
    }

    /**
     * Tests Tar::handlesCompression
     */
    public function testHandlesCompression()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $tar  = new Tar($path);

        $this->assertFalse($tar->handlesCompression());

        $tar->useCompression('zip');

        $this->assertFalse($tar->handlesCompression());

        $tar->useCompression('gzip');

        $this->assertTrue($tar->handlesCompression());
    }
}
