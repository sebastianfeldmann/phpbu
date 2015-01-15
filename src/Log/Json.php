<?php
namespace phpbu\Log;

use phpbu\App\Exception;
use phpbu\App\Listener;
use phpbu\App\Result;
use phpbu\Log\Logger;
use phpbu\Log\Printer;

/**
 * Json Logger
 *
 * @package phpbu
 * @subpackage Log
 * @author Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright 2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license http://www.opensource.org/licenses/BSD-3-Clause The BSD 3-Clause License
 * @link http://www.phpbu.de/
 * @since Class available since Release 1.0.0
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
     *
     * @see \phpbu\Log\Logger::setup
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
     * @see \phpbu\App\Listener::phpbuStart()
     */
    public function phpbuStart($settings)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::phpbuEnd()
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
     * @see \phpbu\App\Listener::backupStart()
     */
    public function backupStart($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::backupEnd()
     */
    public function backupEnd($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::backupFailed()
     */
    public function backupFailed($backup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::checkStart()
     */
    public function checkStart($check)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::checkEnd()
     */
    public function checkEnd($check)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::checkFailed()
     */
    public function checkFailed($check)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::syncStart()
     */
    public function syncStart($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::syncEnd()
     */
    public function syncEnd($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::syncSkipped()
     */
    public function syncSkipped($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::syncFailed()
     */
    public function syncFailed($sync)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::cleanupStart()
     */
    public function cleanupStart($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::cleanupEnd()
     */
    public function cleanupEnd($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::cleanupSkipped()
     */
    public function cleanupSkipped($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::cleanupFailed()
     */
    public function cleanupFailed($cleanup)
    {
        // do something fooish
    }

    /**
     *
     * @see \phpbu\App\Listener::debug()
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
     * Get error informations
     *
     * @param \phpbu\App\Result $result
     * @return array
     */
    protected function extractErrors(Result $result)
    {
        $errors = array();
        /* @var $e Exception */
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
     * Returns backup informations
     *
     * @param \phpbu\App\Result $result
     * @return array
     */
    protected function extractBackups(Result $result)
    {
        $output = array();
        $backups = $result->getBackups();
        if (count($backups) > 0) {
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
