<?php
namespace phpbu\App\Log;

use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Result;

/**
 * Webhook Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Cees Vogel <jcvogel@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release ???
 */
class Webhook implements Listener, Logger
{
    /**
     * @var array: List of all debug messages
     */
    protected $debug = [];

    /**
     * @var array: options for Webhook
     */
    protected $options = [];

    /**
     * @var null: timer start
     */
    protected $start = null;

    /**
     * Constructor will only set the start time to be able to log duration
     */
    public function __construct()
    {
        $this->start = microtime(true);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'phpbu.debug' => 'onDebug',
            'phpbu.app_end' => 'onPhpbuEnd',
        ];
    }

    /**
     * Setup the logger.
     *
     * @see    \phpbu\App\Log\Logger::setup
     * @param  array $options
     * @throws \phpbu\App\Exception
     */
    public function setup(array $options)
    {
        if (empty($options['uri'])) {
            throw new Exception('no uri given');
        }
        $defaults = [
            'method' => 'GET',
            'xml-root' => 'root',
            'content-type' => 'multipart/form-data',
            'auth-user' => '',
            'auth-pwd' => '',
            'jsonOutput' => ''
        ];
        $this->options = array_merge($defaults, $options);
    }

    /**
     * phpbu end event.
     *
     * @param \phpbu\App\Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $result = $event->getResult();

        $output = $this->getOutput($result);

        $this->execute($output);
    }

    /**
     * Debugging.
     *
     * @param \phpbu\App\Event\Debug $event
     */
    public function onDebug(Event\Debug $event)
    {
        $this->debug[] = $event->getMessage();
    }

    /**
     * Method will use the input Result to replace the placeholders in $this->jsonOutput or return an array with
     * the default values.
     *
     * @param $result \phpbu\App\Result
     * @return array: will return array placeholders are replaced with correct data
     */
    public function getOutput($result) : array
    {
        $vars = [
            '__status__' => $result->allOk() ? 0 : 1,
            '__timestamp__' => time(),
            '__duration__' => round(microtime(true) - $this->start, 4),
            '__errors__' => $this->extractErrors($result),
            '__backups__' => $this->extractBackups($result),
            '__errorcount__' => count($result->getErrors())
        ];

        if (!empty($this->options['jsonOutput']) && is_string($this->options['jsonOutput'])) {
            // first convert to array. Simple str_replace won't work because of arrays in backups and errors.
            $outputArray = json_decode(html_entity_decode($this->options['jsonOutput']), true);
            // check if json_decode succeeded, otherwise return default parameters
            if ($outputArray) {
                // only value where valuestring equals vars key is supported.
                array_walk_recursive($outputArray, function (&$value, &$key) use ($vars) {
                    if (strpos($value, '__') === 0) {
                        $value = $vars[$value];
                    }
                });
                return $outputArray;
            }
        }
        $default = [
            'status' => $vars['__status__'],
            'timestamp' => $vars['__timestamp__'],
            'duration' => $vars['__duration__'],
            'errorcount' => $vars['__errorcount__']
        ];

        return $default;
    }

    public function execute($output)
    {
        $ch = curl_init();
        $uri = $this->options['uri'];
        if (strtoupper($this->options['method']) == 'GET') {
            $uri .= '?' . http_build_query($output);
        }
        curl_setopt($ch, CURLOPT_URL, $uri);
        if (strtoupper($this->options['method']) == 'POST') {
            $output = $this->formatPostOutput($output);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $output);
        }
        if (!empty($this->options['auth-user']) && !empty($this->options['auth-pwd'])) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->options['auth-user'] . ":" . $this->options['auth-pwd']);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . $this->options['content-type'],
            'Accept: ' . $this->options['content-type']
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Method will format the output data in the format requested by the content-type
     * @param $output: different formats
     */
    public function formatPostOutput($output)
    {
        switch ($this->options['content-type']) {
            case 'application/json':
                return json_encode($output);
            case 'text/xml':
            case 'application/xml':
                $xml = new \SimpleXMLElement(sprintf('<%s/>', $this->options['xml-root']));
                $this->toXml($xml, $output);
                return $xml->asXML();
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
            default:
                return http_build_query($output);
        }
    }

    /**
     * Simple toXml function
     *
     * @author Francis Lewis: https://stackoverflow.com/a/19987539
     * @param SimpleXMLElement $object
     * @param array $data
     */
    private function toXml(\SimpleXMLElement $object, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $new_object = $object->addChild($key);
                $this->toXml($new_object, $value);
            } else {
                // if the key is an integer, it needs text with it to actually work.
                if ($key == (int)$key) {
                    $key = "key_$key";
                }

                $object->addChild($key, $value);
            }
        }
    }


    /**
     * Get error information.
     *
     * @param \phpbu\App\Result $result
     * @return array
     */
    protected function extractErrors(Result $result) : array
    {
        $errors = [];
        /** @var \Exception $e */
        foreach ($result->getErrors() as $e) {
            $errors[] = [
                'class' => get_class($e),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
        return $errors;
    }

    /**
     * Return backup information.
     *
     * @param  \phpbu\App\Result $result
     * @return array
     */
    protected function extractBackups(Result $result) : array
    {
        $output = [];
        $backups = $result->getBackups();
        if (count($backups) > 0) {
            /** @var \phpbu\App\Result\Backup $backup */
            foreach ($backups as $backup) {
                $output[] = [
                    'name' => $backup->getName(),
                    'status' => $backup->wasSuccessful() ? 0 : 1,
                    'checks' => [
                        'executed' => $backup->checkCount(),
                        'failed' => $backup->checkCountFailed()
                    ],
                    'crypt' => [
                        'executed' => $backup->cryptCount(),
                        'skipped' => $backup->cryptCountSkipped(),
                        'failed' => $backup->cryptCountFailed()
                    ],
                    'syncs' => [
                        'executed' => $backup->syncCount(),
                        'skipped' => $backup->syncCountSkipped(),
                        'failed' => $backup->syncCountFailed()
                    ],
                    'cleanups' => [
                        'executed' => $backup->cleanupCount(),
                        'skipped' => $backup->cleanupCountSkipped(),
                        'failed' => $backup->cleanupCountFailed()
                    ]
                ];
            }
        }
        return $output;
    }
}
