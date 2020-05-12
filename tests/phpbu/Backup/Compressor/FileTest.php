<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * File compressor test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class FileTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests File::getExecutable
     */
    public function testDefault()
    {
        $target = $this->createTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompression')
               ->willReturn($this->createCompressionMock('gzip', 'gz'));

        $file       = new File(__FILE__, PHPBU_TEST_BIN);
        $executable = $file->getExecutable($target);

        $this->assertEquals(PHPBU_TEST_BIN . '/gzip -f \'' . __FILE__ . '\'', $executable->getCommand());
    }

    /**
     * Tests File::__construct
     */
    public function testNoPath()
    {
        $this->expectException('phpbu\App\Exception');
        $file = new File('');
    }

    /**
     * Tests File::__construct
     */
    public function testNoFile()
    {
        $this->expectException('phpbu\App\Exception');
        $result = $this->getAppResultMock();
        $target = $this->createTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompression')
               ->willReturn($this->createCompressionMock('gzip', 'gz'));

        $file = new File(__DIR__);
        $file->compress($target, $result);
    }

    /**
     * Tests File::compress
     */
    public function testCompressOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'gzip'));

        $appResult = $this->getAppResultMock();
        $target    = $this->createTargetMock(__FILE__, __FILE__ . '.gz');
        $target->expects($this->once())
               ->method('getCompression')
               ->willReturn($this->createCompressionMock('gzip', 'gz'));

        $dir = new File(__FILE__, PHPBU_TEST_FILES . '/bin', $runner);
        $dir->compress($target, $appResult);
    }

    /**
     * Tests File::compress
     */
    public function testCompressFailTargetUncompressed()
    {
        $this->expectException('phpbu\App\Exception');
        $dir       = new File(__FILE__);
        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();

        $dir->compress($target, $appResult);
    }

    /**
     * Tests File::compress
     */
    public function testCompressFail()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'gzip'));

        $appResult     = $this->getAppResultMock();
        $target        = $this->createTargetMock(__FILE__, __FILE__ . '.gz');
        $target->method('getCompression')
               ->willReturn($this->createCompressionMock('gzip', 'gz'));

        $file = new File(__FILE__, PHPBU_TEST_FILES . '/bin', $runner);
        $file->compress($target, $appResult);
    }
}
