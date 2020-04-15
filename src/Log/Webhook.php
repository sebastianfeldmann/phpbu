<?php
namespace phpbu\App\Log;

use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Result;
use phpbu\App\Util\Arr;
use Throwable;

/**
 * Webhook Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Cees Vogel <jcvogel@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Webhook implements Listener, Logger
{
    /**
     * Start time
     *
     * @var float
     */
    private $start;

    /**
     * Uri to call
     *
     * @var string
     */
    private $uri;

    /**
     * Request method GET|POST
     *
     * @var string
     */
    private $method;

    /**
     * Request timeout (seconds)
     *
     * @var int
     */
    private $timeout;

    /**
     * Basic auth username
     *
     * @var string
     */
    private $username;

    /**
     * Basic auth password
     *
     * @var string
     */
    private $password;

    /**
     * Request content type
     *
     * @var string
     */
    private $contentType;

    /**
     * Body template to use
     *
     * @var string
     */
    private $template;

    /**
     * List of available default formatter
     *
     * @var array
     */
    private $availableFormatter = [
        'multipart/form-data' => '\\phpbu\\App\\Log\\ResultFormatter\\FormData',
        'application/json'    => '\\phpbu\\App\\Log\\ResultFormatter\\Json',
        'application/xml'     => '\\phpbu\\App\\Log\\ResultFormatter\\Xml'
    ];

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
     *  - The method name to call (priority defaults to 0)
     *  - An array composed of the method name to call and the priority
     *  - An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
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

        // PHP >7.2 deprecated the filter options and enabled them by default
        $filterOptions = version_compare(PHP_VERSION, '7.2.0', '<')
                       ? FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED
                       : null;

        if (!filter_var($options['uri'], FILTER_VALIDATE_URL, $filterOptions)) {
            throw new Exception('webhook URI is invalid');
        }

        $this->uri             = $options['uri'];
        $this->method          = Arr::getValue($options, 'method', 'GET');
        $this->username        = Arr::getValue($options, 'username', '');
        $this->password        = Arr::getValue($options, 'password', '');
        $this->template        = Arr::getValue($options, 'template', '');
        $this->contentType     = Arr::getValue($options, 'contentType', 'multipart/form-data');
        $this->timeout         = Arr::getValue($options, 'timeout', '');
    }

    /**
     * phpbu end event.
     *
     * @param \phpbu\App\Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $result    = $event->getResult();
        $data      = $this->getQueryStringData($result);
        $uri       = $this->method === 'GET' ? $this->buildGetUri($data) : $this->uri;
        $formatter = $this->getBodyFormatter();
        $body      = $formatter->format($result);

        $this->fireRequest($uri, $body);
    }

    /**
     * Builds the final request uri for GET requests.
     *
     * @param  array $data
     * @return string
     */
    private function buildGetUri(array $data) : string
    {
        $glue = strpos($this->uri, '?') !== false ? '&' : '?';
        return $this->uri . $glue . http_build_query($data);
    }

    /**
     * Return the request body template.
     * If template and body are set the body supersedes the template setting.
     *
     * @return \phpbu\App\Log\ResultFormatter
     * @throws \phpbu\App\Exception
     */
    private function getBodyFormatter() : ResultFormatter
    {
        if (!empty($this->template)) {
            return new ResultFormatter\Template($this->template);
        }

        if (!isset($this->availableFormatter[$this->contentType])) {
            throw new Exception('no default formatter for content-type: ' . $this->contentType);
        }
        $class = $this->availableFormatter[$this->contentType];
        return new $class();
    }

    /**
     * Returns some basic statistics as GET query string.
     *
     * @param  \phpbu\App\Result $result
     * @return array
     */
    private function getQueryStringData(Result $result) : array
    {
        $end = microtime(true);

        return [
            'status'    => $result->allOk() ? 0 : 1,
            'timestamp' => (int) $this->start,
            'duration'  => round($end - $this->start, 4),
            'err-cnt'   => $result->errorCount(),
            'bak-cnt'   => count($result->getBackups()),
            'bak-fail'  => $result->backupsFailedCount(),
        ];
    }


    /**
     * Execute the request to the webhook uri.
     *
     * @param  string $uri
     * @param  string $body
     * @throws \phpbu\App\Exception
     */
    protected function fireRequest(string $uri, string $body = '')
    {
        $headers = [];
        $options = [
            'http' => [
                'method'  => strtoupper($this->method),
            ]
        ];

        if (!empty($body)) {
            $headers[]                  = 'Content-Type: ' . $this->contentType;
            $options['http']['content'] = $body;
        }

        if (!empty($this->timeout)) {
            $options['http']['timeout'] = $this->timeout;
        }

        if (!empty($this->username)) {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        $options['http']['header'] = implode("\r\n", $headers);
        $context                   = stream_context_create($options);

        try {
            file_get_contents($uri, false, $context);
        } catch (Throwable $t) {
            throw new Exception('could not reach webhook: ' . $this->uri);
        }
    }
}
