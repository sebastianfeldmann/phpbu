<?php
namespace phpbu\App\Log\ResultFormatter;

/**
 * Template Formatter Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class TemplateTestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test Template::__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testMissingTemplate()
    {
        $path      = './some/stupid/file.tpl';
        $formatter = new Template($path);
    }

    /**
     * Tests Template::format
     */
    public function testFormat()
    {
        $path      = PHPBU_TEST_FILES . '/misc/webhook.tpl';
        $result    = $this->getResultMock();
        $formatter = new Template($path);
        $body      = $formatter->format($result);

        $this->assertTrue(strpos($body, '<return>0</return>') !== false, 'result should exist');
        $this->assertTrue(strpos($body, '<status>0</status>') !== false, 'status should exist');
        $this->assertTrue(!strpos($body, '%%'), 'no % should exist anymore');
    }

    /**
     * Create a app result mock.
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->getMockBuilder('\\phpbu\\App\\Result')->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('started')->willReturn(microtime(true));
        $result->expects($this->once())->method('allOk')->willReturn(true);
        $result->expects($this->once())->method('backupsFailedCount')->willReturn(0);
        $result->expects($this->once())->method('errorCount')->willReturn(1);
        $result->expects($this->once())->method('getErrors')->willReturn([new \Exception('foo bar')]);
        $result->expects($this->exactly(2))->method('getBackups')->willReturn([$this->getBackupResultMock()]);

        return $result;
    }

    /**
     * Create a backup result mock.
     *
     * @return \phpbu\App\Result\Backup
     */
    protected function getBackupResultMock()
    {
        $backup = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')->disableOriginalConstructor()->getMock();
        $backup->method('getName')->willReturn('foo');
        $backup->method('allOk')->willReturn(true);
        $backup->method('checkCount')->willReturn(0);
        $backup->method('checkCountFailed')->willReturn(0);
        $backup->method('syncCount')->willReturn(0);
        $backup->method('syncCountSkipped')->willReturn(0);
        $backup->method('syncCountFailed')->willReturn(0);
        $backup->method('cleanupCount')->willReturn(0);
        $backup->method('cleanupCountSkipped')->willReturn(0);
        $backup->method('cleanupCountFailed')->willReturn(0);

        return $backup;
    }
}
