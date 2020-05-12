<?php
namespace phpbu\App\Log\ResultFormatter;

use PHPUnit\Framework\TestCase;

/**
 * Xml Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class XmlTest extends TestCase
{
    /**
     * Tests Xml::format
     */
    public function testFormat()
    {
        $result    = $this->getResultMock();
        $formatter = new Xml();
        $xml       = $formatter->format($result);

        $this->assertNotEmpty($xml);

        $simpleXml = simplexml_load_string($xml);

        $this->assertInstanceOf(\SimpleXMLElement::class, $simpleXml);
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
