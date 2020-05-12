<?php
namespace phpbu\App\Log\ResultFormatter;

use PHPUnit\Framework\TestCase;

/**
 * Json Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class JsonTest extends TestCase
{
    /**
     * Tests Json::format
     */
    public function testFormat()
    {
        $result    = $this->getResultMock();
        $formatter = new Json();
        $json      = $formatter->format($result);

        $this->assertTrue(!empty($json));

        $raw = json_decode($json, true);

        $this->assertEquals(0, $raw['status']);
        $this->assertCount(1, $raw['errors']);
        $this->assertCount(1, $raw['backups']);
        $this->assertEquals('foo', $raw['backups'][0]['name']);
    }

    /**
     * Create a app result mock
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
     * Create a backup result mock
     *
     * @return \phpbu\App\Result\Backup
     */
    protected function getBackupResultMock()
    {
        $backup = $this->createMock(\phpbu\App\Result\Backup::class);
        $backup->expects($this->once())->method('getName')->willReturn('foo');
        $backup->expects($this->once())->method('allOk')->willReturn(true);
        $backup->expects($this->once())->method('checkCount')->willReturn(0);
        $backup->expects($this->once())->method('checkCountFailed')->willReturn(0);
        $backup->expects($this->once())->method('syncCount')->willReturn(0);
        $backup->expects($this->once())->method('syncCountSkipped')->willReturn(0);
        $backup->expects($this->once())->method('syncCountFailed')->willReturn(0);
        $backup->expects($this->once())->method('cleanupCount')->willReturn(0);
        $backup->expects($this->once())->method('cleanupCountSkipped')->willReturn(0);
        $backup->expects($this->once())->method('cleanupCountFailed')->willReturn(0);

        return $backup;
    }
}
