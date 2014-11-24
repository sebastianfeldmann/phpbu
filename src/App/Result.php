<?php
namespace phpbu\App;

use phpbu\App\Listener;

/**
 * Runner result.
 *
 * Heavily 'inspired' by Sebastian Bermann's phpunit PHPUnit_Framework_TestResult.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Result
{
    /**
     * List of Logging listeners
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * List of errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * @var boolean
     */
    protected $stopOnError = false;

    /**
     * @var boolean
     */
    protected $backupFailed = false;

    /**
     * @var boolean
     */
    protected $checkFailed = false;

    /**
     * @var boolean
     */
    protected $syncFailed = false;

    /**
     * @return boolean
     */
    public function wasSuccessful()
    {
        return empty($this->errors);
    }

    /**
     * Return currnet error count.
     *
     * @return integer
     */
    public function errorCount()
    {
        return count($this->errors);
    }

    /**
     * @param array $settings
     */
    public function phpbuStart(array $settings)
    {
        foreach ($this->listeners as $l) {
            $l->phpbuStart($settings);
        }
    }

    /**
     */
    public function phpbuEnd()
    {
        foreach ($this->listeners as $l) {
            $l->phpbuEnd();
        }
    }

    /**
     * @param array $backup
     */
    public function backupStart($backup)
    {
        foreach ($this->listeners as $l) {
            $l->backupStart($backup);
        }
    }

    /**
     * @param array $backup
     */
    public function backupFailed($backup)
    {
        $this->backupFailed = true;
        foreach ($this->listeners as $l) {
            $l->backupFailed($backup);
        }
    }

    /**
     * @param array $backup
     */
    public function backupEnd($backup)
    {
        foreach ($this->listeners as $l) {
            $l->backupEnd($backup);
        }
    }

    /**
     * @param array $check
     */
    public function checkStart($check)
    {
        foreach ($this->listeners as $l) {
            $l->checkStart($check);
        }
    }

    /**
     * @param array $check
     */
    public function checkFailed($check)
    {
        $this->checkFailed = true;
        foreach ($this->listeners as $l) {
            $l->checkFailed($check);
        }
    }

    /**
     * @param array $check
     */
    public function checkEnd($check)
    {
        foreach ($this->listeners as $l) {
            $l->checkEnd($check);
        }
    }

    /**
     * @param array $sync
     */
    public function syncStart($sync)
    {
        foreach ($this->listeners as $l) {
            $l->syncStart($sync);
        }
    }

    /**
     * @param array $sync
     */
    public function syncFailed($sync)
    {
        $this->syncFailed = true;
        foreach ($this->listeners as $l) {
            $l->syncFailed($sync);
        }
    }

    /**
     * @param array $cleanup
     */
    public function syncEnd($sync)
    {
        foreach ($this->listeners as $l) {
            $l->syncEnd($sync);
        }
    }
    /**
     * @param array $cleanup
     */
    public function cleanupStart($cleanup)
    {
        foreach ($this->listeners as $l) {
            $l->cleanupStart($cleanup);
        }
    }

    /**
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup)
    {
        $this->cleanupFailed = true;
        foreach ($this->listeners as $l) {
            $l->cleanupFailed($cleanup);
        }
    }

    /**
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup)
    {
        foreach ($this->listeners as $l) {
            $l->cleanupEnd($cleanup);
        }
    }

    /**
     * @param string $msg
     */
    public function debug($msg)
    {
        foreach ($this->listeners as $l) {
            $l->debug($msg);
        }
    }

    /**
     * Registers a Listener.
     *
     * @param \phpbu\App\Listener
     */
    public function addListener(Listener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a Listener.
     *
     * @author Sebastian Bergmann <sebastian@phpunit.de>
     * @param  \phpbu\App\Listener $listener
     */
    public function removeListener(Listener $listener)
    {
        foreach ($this->listeners as $key => $l) {
            if ($listener === $l) {
                unset($this->listeners[$key]);
            }
        }
    }
}
