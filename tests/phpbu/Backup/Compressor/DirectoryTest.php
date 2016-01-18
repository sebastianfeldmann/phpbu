<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\CliTest;

/**
 * Directory compressor test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class DirectoryTest extends CliTest
{
    /**
     * Tests Directory::getExecutable
     */
    public function testDefault()
    {
        $path   = $this->getBinDir();
        $dir    = new Directory(__DIR__, $path);
        $target = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $executable = $dir->getExecutable($target);
        $cmd        = $executable->getCommandLine();

        $this->assertEquals(
            '(' . $path . '/tar -zcf \'' . __FILE__ . '.gz\' -C \'' . __DIR__ .  '\' \'.\' 2> /dev/null'
          . ' && rm -rf \'' . __DIR__ . '\' 2> /dev/null)',
            $cmd
        );
    }

    /**
     * Tests Directory::__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoPath()
    {
        $dir = new Directory('');
    }

    /**
     * Tests Directory::__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoDir()
    {
        $dir    = new Directory(__FILE__);
        $result = $this->getAppResultMock();
        $target = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $dir->compress($target, $result);
    }

    /**
     * Tests Directory::compress
     */
    public function testCompressOk()
    {
        $dir       = new Directory(__DIR__);
        $cliResult = $this->getCliResultMock(0, 'tar');
        $target    = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $tar       = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();
        $tar->method('run')->willReturn($cliResult);


        $dir->setExecutable($tar);
        $dir->compress($target, $appResult);
    }

    /**
     * Tests Directory::compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressFailTargetUncompressed()
    {
        $dir       = new Directory(__DIR__);
        $target    = $this->getTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();

        $dir->compress($target, $appResult);
    }

    /**
     * Tests Directory::compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressFail()
    {
        $dir       = new Directory(__DIR__);
        $cliResult = $this->getCliResultMock(1, 'tar');
        $target    = $this->getTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompressor')->willReturn($this->getCompressorMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $tar       = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();
        $tar->method('run')->willReturn($cliResult);


        $dir->setExecutable($tar);
        $dir->compress($target, $appResult);
    }
}
