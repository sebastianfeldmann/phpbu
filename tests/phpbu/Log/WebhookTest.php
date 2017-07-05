<?php
namespace phpbu\App\Log;

/**
 * Webhook Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Cees Vogel <jcvogel@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class WebhookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Webhook::getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $events = Webhook::getSubscribedEvents();
        $this->assertEquals(2, count($events));
    }

    /**
     * Tests default output from Webhook::getOutput
     */
    public function testGetOutputDefault()
    {
        $result = $this->getResultMock();
        $webhook = new Webhook();

        $output = $webhook->getOutput($result);

        $this->assertEquals($output['status'], 0);
        $this->assertEquals($output['errorcount'], 1);
        $this->assertTrue(is_int($output['timestamp']));
        $this->assertTrue(is_float($output['duration']));
        $this->assertFalse(isset($output['__timestamp__']));
    }

    /**
     * Tests POST data output formatter for json
     */
    public function testFormatPostOutputJson()
    {
        $webhook = new Webhook();
        $output = ['test1' => 'test2', 'test3' => ['test4' => 'test5']];
        $options = ['uri' => 'http://not.found', 'content-type' => 'application/json'];
        $webhook->setup($options);
        $result = $webhook->formatPostOutput($output);
        $this->assertEquals('{"test1":"test2","test3":{"test4":"test5"}}', $result);
    }

    /**
     * Tests POST data output formatter for xml
     */
    public function testFormatPostOutputXml()
    {
        $webhook = new Webhook();
        $output = ['test1' => 'test2', 'test3' => ['test4' => 'test5']];
        $options = ['uri' => 'http://not.found', 'content-type' => 'application/xml'];
        $webhook->setup($options);
        $result = $webhook->formatPostOutput($output);
        $expected = '<?xml version="1.0"?>' . PHP_EOL .
            '<root><key_test1>test2</key_test1><test3><key_test4>test5</key_test4></test3></root>' . PHP_EOL;
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests POST data output formatter for query form
     */
    public function testFormatPostOutputQueryForm()
    {
        $webhook = new Webhook();
        $output = ['test1' => 'test2', 'test3' => ['test4' => 'test5']];
        $options = ['uri' => 'http://not.found', 'content-type' => 'application/x-www-form-urlencoded'];
        $webhook->setup($options);
        $result = $webhook->formatPostOutput($output);
        $this->assertEquals(http_build_query($output), $result);
    }

    /**
     * Tests POST data output formatter without specified content-type
     */
    public function testFormatPostOutputDefault()
    {
        $webhook = new Webhook();
        $output = ['test1' => 'test2', 'test3' => ['test4' => 'test5']];
        $options = ['uri' => 'http://not.found'];
        $webhook->setup($options);
        $result = $webhook->formatPostOutput($output);
        $this->assertEquals(http_build_query($output), $result);
    }

    /**
     * Tests output from Webhook::getOutput when jsonOutput is specified.
     */
    public function testGetOutputFromJson()
    {
        $result = $this->getResultMock();
        $outputOption = [
            'key' => [
                'key2' => [
                    'time' => '__timestamp__',
                ],
                'key3' => '__duration__'
            ]
        ];
        $outputOptionString = '{&quot;key&quot;:{&quot;key2&quot;:{&quot;time&quot;:&quot;__timestamp__&quot;}' .
            ',&quot;key3&quot;:&quot;__duration__&quot;}}';
        // make sure we encoded the array right:
        $this->assertEquals($outputOption, json_decode(html_entity_decode($outputOptionString), true));

        $webhook = new Webhook();
        $webhook->setup(['uri' => 'http://not.found', 'jsonOutput' => $outputOptionString]);
        $output = $webhook->getOutput($result);

        $this->assertTrue(isset($output['key']['key2']['time']));
        $this->assertTrue(is_int($output['key']['key2']['time']));
        $this->assertFalse($output['key']['key3'] === '__duration__');
    }

    /**
     * Method will test if default output is returned when json string is invalid.
     */
    public function testGetOutputFromJsonWithWrongJson()
    {
        $result = $this->getResultMock();

        $outputOptionString = '{this is most definitely an incorrect json string}';

        $webhook = new Webhook();
        $webhook->setup(['uri' => 'http://not.found', 'jsonOutput' => $outputOptionString]);
        $output = $webhook->getOutput($result);

        $this->assertEquals($output['status'], 0);
    }

    /**
     * Create an app result mock
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
