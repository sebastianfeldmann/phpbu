<?php
namespace phpbu\App\Log;

use PHPUnit\Framework\TestCase;

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
class WebhookTest extends TestCase
{
    /**
     * Tests Webhook::getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $events = Webhook::getSubscribedEvents();
        $this->assertCount(1, $events);
    }

    /**
     * Tests Webhook::setup
     */
    public function testSetupNoTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $json = new Webhook();
        $json->setup([]);
    }

    /**
     * Tests Webhook::setup
     */
    public function testUriMustBeValid()
    {
        $this->expectException('phpbu\App\Exception');
        $json = new Webhook();
        $json->setup(['uri' => 'not a URI']);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testGet()
    {
        $this->expectException('phpbu\App\Exception');
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'https://webhook.fail.uri/hook';
        $json = new Webhook();
        $json->setup(['uri' => $uri]);

        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testBasicAuth()
    {
        $this->expectException('phpbu\App\Exception');
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'https://webhook.fail.uri/hook';
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
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'file://' . PHPBU_TEST_FILES . '/misc/webhook.fake.uri';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/json', 'method' => 'post']);


        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testPostDefaultJson()
    {
        $this->expectException('phpbu\App\Exception');
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'https://webhook.fail.uri/hook';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/json', 'method' => 'post']);


        $json->onPhpbuEnd($phpbuEndEvent);
    }

    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testPostXmlTemplate()
    {
        $this->expectException('phpbu\App\Exception');
        // result mock
        $result = $this->getResultMock();

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'https://webhook.fail.uri/hook';
        $path = PHPBU_TEST_FILES . '/misc/webhook.tpl';
        $json = new Webhook();
        $json->setup(['uri' => $uri, 'contentType' => 'application/xml', 'method' => 'post', 'template' => $path]);


        $json->onPhpbuEnd($phpbuEndEvent);
    }


    /**
     * Tests Webhook::onPhpbuEnd
     */
    public function testPostNoFormatter()
    {
        $this->expectException('phpbu\App\Exception');
        $this->expectExceptionMessage('no default formatter for content-type: application/html');
        // result mock
        $result = $this->getResultMock(false);

        // phpbu end event mock
        $phpbuEndEvent = $this->createMock(\phpbu\App\Event\App\End::class);
        $phpbuEndEvent->method('getResult')->willReturn($result);

        $uri  = 'https://webhook.fail.uri/hook';
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
        $result = $this->createMock(\phpbu\App\Result::class);
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
