<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Exception;
use PHPUnit\Framework\TestCase;

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
class TemplateTestTest extends TestCase
{
    /**
     * Test Template::__construct
     */
    public function testMissingTemplate()
    {
        $this->expectException(Exception::class);

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

        $this->assertStringContainsString('<return>0</return>', $body, 'result should exist');
        $this->assertStringContainsString('<status>0</status>', $body, 'status should exist');
        $this->assertStringNotContainsString('%%', $body, 'no % should exist anymore');
    }

    /**
     * Create a app result mock.
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->createMock(\phpbu\App\Result::class);
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
        $backup = $this->createMock(\phpbu\App\Result\Backup::class);
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
