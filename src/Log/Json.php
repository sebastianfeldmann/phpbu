<?php
namespace phpbu\App\Log;

use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Result;

/**
 * Json Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Json extends File implements Listener, Logger
{
    /**
     * List of all debug messages
     *
     * @var array
     */
    protected $debug = [];

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
            'phpbu.debug'   => 'onDebug',
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
        if (empty($options['target'])) {
            throw new Exception('no target given');
        }
        $this->setOut($options['target']);
    }

    /**
     * phpbu end event.
     *
     * @param \phpbu\App\Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $result = $event->getResult();
        $output = [
            'status'    => $result->allOk() ? 0 : 1,
            'timestamp' => time(),
            'errors'    => $this->extractErrors($result),
            'debug'     => $this->debug,
            'backups'   => $this->extractBackups($result)
        ];
        $this->write($output);
        $this->close();
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
     * Write a buffer to file.
     *
     * @param array $buffer
     */
    public function write($buffer)
    {
        parent::write(json_encode($buffer));
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
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
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
                    'name'   => $backup->getName(),
                    'status' => $backup->wasSuccessful() ? 0 : 1,
                    'checks' => [
                        'executed' => $backup->checkCount(),
                        'failed'   => $backup->checkCountFailed()
                    ],
                    'crypt' => [
                        'executed' => $backup->cryptCount(),
                        'skipped'  => $backup->cryptCountSkipped(),
                        'failed'   => $backup->cryptCountFailed()
                    ],
                    'syncs' => [
                        'executed' => $backup->syncCount(),
                        'skipped'  => $backup->syncCountSkipped(),
                        'failed'   => $backup->syncCountFailed()
                    ],
                    'cleanups' => [
                        'executed' => $backup->cleanupCount(),
                        'skipped'  => $backup->cleanupCountSkipped(),
                        'failed'   => $backup->cleanupCountFailed()
                    ]
                ];
            }
        }
        return $output;
    }
}
