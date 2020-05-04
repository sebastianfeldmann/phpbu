<?php
namespace phpbu\App\Log\ResultFormatter;

use function GuzzleHttp\Psr7\parse_query;
use PHPUnit\Framework\TestCase;

/**
 * FormData Formatter Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class FormDataTest extends TestCase
{
    /**
     * Tests FormData::format
     */
    public function testFormat()
    {
        $result      = $this->getResultMock();
        $formatter   = new FormData();
        $queryString = $formatter->format($result);
        $rawData     = parse_query($queryString);

        $this->assertNotEmpty($queryString);
        $this->assertEquals(0, $rawData['status']);
        $this->assertEquals(1, $rawData['errorCount']);
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
        $result->expects($this->once())->method('getBackups')->willReturn([$this->getBackupResultMock()]);

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
