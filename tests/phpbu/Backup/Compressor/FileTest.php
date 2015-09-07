<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\CliTest;

/**
 * File compressor test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class FileTest extends CliTest
{
    /**
     * Tests File::getExecutable
     */
    public function testDefault()
    {
        $path   = $this->getBinDir();
        $dir    = new File(__FILE__, $path);
        $target = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $executable = $dir->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals($path . '/gzip -f \'' . __FILE__ . '\' 2> /dev/null', $cmd);
    }

    /**
     * Tests File::__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoPath()
    {
        $dir = new File('');
    }

    /**
     * Tests File::__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoFile()
    {
        $file = new File(__DIR__);
    }

    /**
     * Tests File::compress
     */
    public function testCompressOk()
    {
        $dir       = new File(__FILE__);
        $cliResult = $this->getCliResultMock(0, 'gzip');
        $target    = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $gzip      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Compressor')
                          ->disableOriginalConstructor()
                          ->getMock();
        $gzip->method('run')->willReturn($cliResult);


        $dir->setExecutable($gzip);
        $dir->compress($target, $appResult);
    }

    /**
     * Tests File::compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressFailTargetUncompressed()
    {
        $dir       = new File(__FILE__);
        $target    = $this->getTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();

        $dir->compress($target, $appResult);
    }

    /**
     * Tests File::compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressFail()
    {
        $dir       = new File(__FILE__);
        $cliResult = $this->getCliResultMock(1, 'gzip');
        $target    = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $gzip      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Compressor')
                          ->disableOriginalConstructor()
                          ->getMock();
        $gzip->method('run')->willReturn($cliResult);


        $dir->setExecutable($gzip);
        $dir->compress($target, $appResult);
    }
}
