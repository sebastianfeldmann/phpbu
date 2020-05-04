<?php
namespace phpbu\App\Log;

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
 * @since      Class available since Release 3.0.0
 */
class JsonTest extends TestCase
{
    /**
     * Tests Json::getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $events = Json::getSubscribedEvents();
        $this->assertCount(2, $events);
    }

    /**
     * Tests Json::setup
     */
    public function testSetupNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $json = new Json();
        $json->setup([]);
    }

    /**
     * Tests Json::onPhpbuEnd
     */
    public function testOutput()
    {
        // result mock
        $result = $this->getResultMock();

        // debug event mock
        $debugEvent = $this->createMock(\phpbu\App\Event\Debug::class);
        $debugEvent->method('getMessage')->willReturn('debug');

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $json = new Json();
        $json->setup(['target' => 'php://output']);

        $json->onDebug($debugEvent);

        ob_flush();
        ob_start();
        $json->onPhpbuEnd($phpbuEndEvent);
        $outputJson = ob_get_clean();
        $outputPHP  = json_decode($outputJson);


        $this->assertInstanceOf(\stdClass::class, $outputPHP);
    }

    /**
     * Tests Json::write
     */
    public function testWrite()
    {
        $json = new Json();
        $json->setup(['target' => 'php://output']);

        ob_flush();
        ob_start();
        $json->write(['foo' => 'bar']);
        $output = ob_get_clean();

        $this->assertEquals('{"foo":"bar"}', $output);
    }

    /**
     * Create a app result mock
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->method('started')->willReturn(microtime(true));
        $result->method('allOk')->willReturn(true);
        $result->method('getErrors')->willReturn([new \Exception('foo bar')]);
        $result->method('getBackups')->willReturn([$this->getBackupResultMock()]);
        $result->method('backupsFailedCount')->willReturn(0);
        $result->method('errorCount')->willReturn(1);

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
        $backup->method('getName')->willReturn('foo');
        $backup->method('wasSuccessful')->willReturn(true);
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
