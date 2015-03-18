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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Json extends Printer implements Listener, Logger
{
    /**
     * List of all debug messages
     *
     * @var array
     */
    protected $debug = array();

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
        return array(
            'phpbu.debug'     => 'onDebug',
            'phpbu.app_end' => 'onPhpbuEnd',
        );
    }

    /**
     * Setup the logger.
     *
     * @see    \phpbu\Log\Logger::setup
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
        $output = array(
            'status'    => $result->allOk() ? 0 : 1,
            'timestamp' => time(),
            'errors'    => $this->extractErrors($result),
            'debug'     => $this->debug,
            'backups'   => $this->extractBackups($result)
        );
        $this->write($output);
        $this->flush();
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
    protected function extractErrors(Result $result)
    {
        $errors = array();
        /** @var \Exception $e */
        foreach ($result->getErrors() as $e) {
            $errors[] = array(
                'class' => get_class($e),
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            );
        }
        return $errors;
    }

    /**
     * Return backup information.
     *
     * @param  \phpbu\App\Result $result
     * @return array
     */
    protected function extractBackups(Result $result)
    {
        $output = array();
        $backups = $result->getBackups();
        if (count($backups) > 0) {
            /** @var \phpbu\App\Result\Backup $backup */
            foreach ($backups as $backup) {
                $output[] = array(
                    'name'   => $backup->getName(),
                    'status' => $backup->wasSuccessful() ? 0 : 1,
                    'checks' => array(
                        'executed' => $backup->checkCount(),
                        'failed'   => $backup->checkCountFailed()
                    ),
                    'syncs' => array(
                        'executed' => $backup->syncCount(),
                        'skipped'  => $backup->syncCountSkipped(),
                        'failed'   => $backup->syncCountFailed()
                    ),
                    'cleanups' => array(
                        'executed' => $backup->cleanupCount(),
                        'skipped'  => $backup->cleanupCountSkipped(),
                        'failed'   => $backup->cleanupCountFailed()
                    )
                );
            }
        }

        return $output;
    }
}
