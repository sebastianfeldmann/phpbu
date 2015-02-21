<?php
namespace phpbu\Log;

use phpbu\App\Exception;
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
     * 
     * @see   \phpbu\App\Listener::phpbuStart()
     * @param array $settings
     */
    public function phpbuStart($settings)
    {
        // do something fooish
    }

    /**
     * 
     * @see   \phpbu\App\Listener::phpbuEnd()
     * @param \phpbu\App\Result $result
     */
    public function phpbuEnd(Result $result)
    {
        $output = array(
            'errors' => $this->extractErrors($result),
            'debug' => $this->debug,
            'backups' => $this->extractBackups($result)
        );
        $this->write($output);
    }

    /**
     *
     * @see   \phpbu\App\Listener::backupStart()
     * @param array $backup
     */
    public function backupStart($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::backupEnd()
     * @param array $backup
     */
    public function backupEnd($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::backupFailed()
     * @param array $backup
     */
    public function backupFailed($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::checkStart()
     * @param array $check
     */
    public function checkStart($check)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::checkEnd()
     * @param array $check
     */
    public function checkEnd($check)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::checkFailed()
     * @param array $check
     */
    public function checkFailed($check)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::syncStart()
     * @param array $sync
     */
    public function syncStart($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::syncEnd()
     * @param array $sync
     */
    public function syncEnd($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::syncSkipped()
     * @param array $sync
     */
    public function syncSkipped($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::syncFailed()
     * @param array $sync
     */
    public function syncFailed($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::cleanupStart()
     * @param array $cleanup
     */
    public function cleanupStart($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::cleanupEnd()
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::cleanupSkipped()
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::cleanupFailed()
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see   \phpbu\App\Listener::debug()
     * @param string $msg
     */
    public function debug($msg)
    {
        $this->debug[] = $msg;
    }

    /**
     *
     * @param string $buffer
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
        /** @var Exception $e */
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
                    'name' => $backup->getName(),
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
