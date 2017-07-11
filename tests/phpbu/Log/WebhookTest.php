<?php
namespace phpbu\App\Log;

/**
 * Webhook Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class WebhookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Webhook::getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $events = Webhook::getSubscribedEvents();
        $this->assertEquals(1, count($events));
    }

    /**
     * Tests Webhook::setup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupNoTarget()
    {
        $json = new Webhook();
        $json->setup([]);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGet()
    {
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fail.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri]);

        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBasicAuth()
    {
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fail.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'username' => 'foo', 'password' => 'bar']);

        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testPostDefaultJsonSuccess()
    {
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fake.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/json', 'method' => 'post']);


        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testPostDefaultJson()
    {
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fail.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/json', 'method' => 'post']);


        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testPostXmlTemplate()
    {
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fail.uri';
        $path = PHPBU_TEST_FILES . '/misc/webhook.tpl';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/xml', 'method' => 'post', 'template' => $path]);


        $json->onPhpbuEnd($phpbuEndEvent);
    }


    /**
     * Tests Webhook::onPhpbuEnd
     *
     * @expectedException        \phpbu\App\Exception
     * @expectedExceptionMessage no default formatter for content-type: application/html
     */
    public function testPostNoFormatter()
    {
        // result mock
        $result = $this->getResultMock(false);

        // phpbu end event mock
        $phpbuEndEvent = $this->getMockBuilder('\\phpbu\\App\\Event\\App\\End')
                              ->disableOriginalConstructor()
                              ->getMock();
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = PHPBU_TEST_FILES . '/misc/webhook.fail.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/html', 'method' => 'post']);


        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Create a app result mock
     *
     * @param  bool $expectCalls
     * @return \phpbu\App\Result
     */
    protected function getResultMock($expectCalls = true)
    {
        $result = $this->getMockBuilder('\\phpbu\\App\\Result')->disableOriginalConstructor()->getMock();
        if ($expectCalls) {
            $result->expects($this->once())->method('started')->willReturn(microtime(true));
            $result->expects($this->once())->method('started')->willReturn(microtime(true));
            $result->expects($this->exactly(2))->method('allOk')->willReturn(true);
        } else {
            $result->method('started')->willReturn(microtime(true));
            $result->method('started')->willReturn(microtime(true));
            $result->method('allOk')->willReturn(true);
        }
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
        $backup = $this->getMockBuilder('\\phpbu\\App\\Result\\Backup')->disableOriginalConstructor()->getMock();
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
