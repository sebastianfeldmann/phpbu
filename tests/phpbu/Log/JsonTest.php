<?php
namespace phpbu\App\Log;

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
class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Json::getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $events = Json::getSubscribedEvents();
        $this->assertEquals(2, count($events));
    }

    /**
     * Tests Json::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupNoTarget()
    {
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
        $debugEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\Debug')
                           ->disableOriginalConstructor()
                           ->getMock();
        $debugEvent->method('getMessage')->willReturn('debug');

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $json = new Json();
        $json->setup(['target' => 'php://output']);

        $json->onDebug($debugEvent);

        ob_flush();
        ob_start();
        $json->onPhpbuEnd($phpbuEndEvent);
        $outputJson = ob_get_clean();
        $outputPHP  = json_decode($outputJson);


        $this->assertTrue($outputPHP instanceof \stdClass);
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
        $result = $this->getMockBuilder('\\phpbu\\App\\Result')->disableOriginalConstructor()->getMock();
        $result->method('allOk')->willReturn(true);
        $result->method('getErrors')->willReturn([new \Exception('foo bar')]);
        $result->method('getBackups')->willReturn([$this->getBackupResultMock()]);

        return $result;
    }

    /**
     * Create a backup result mock
     *
     * @return \phpbu\App\Result\Backup
     */
    protected function getBackupResultMock()
    {
        $backup = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')->disableOriginalConstructor()->getMock();
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
