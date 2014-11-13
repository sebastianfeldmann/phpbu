<?php
namespace phpbu\App;

use phpbu\App\Listener;

/**
 * Runner result
 *
 * @package    phpbu
 * @subpackage App
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
     */
    public function phpbuStart()
    {
        foreach ($this->listeners as $l) {
            $l->phpbuStart();
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
     * @param Backup $backup
     */
    public function backupStart($backup)
    {
        foreach ($this->listeners as $l) {
            $l->backupStart($backup);
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupFailed($backup)
    {
        $this->backupFailed = true;
        foreach ($this->listeners as $l) {
            $l->backupFailed($backup);
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupEnd($backup)
    {
        foreach ($this->listeners as $l) {
            $l->backupEnd($backup);
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityStart($sanity)
    {
        foreach ($this->listeners as $l) {
            $l->sanityStart($sanity);
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityFailed($sanity)
    {
        $this->sanityFailed = true;
        foreach ($this->listeners as $l) {
            $l->sanityFailed($sanity);
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityEnd($sanity)
    {
        foreach ($this->listeners as $l) {
            $l->sanityEnd($sanity);
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncStart($sync)
    {
        foreach ($this->listeners as $l) {
            $l->syncStart($sync);
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncFailed($sync)
    {
        $this->syncFailed = true;
        foreach ($this->listeners as $l) {
            $l->syncFailed($sync);
        }
    }

    /**
     * @param Sysc $sync
     */
    public function syncEnd($sync)
    {
        foreach ($this->listeners as $l)
        {
            $l->syncEnd($sync);
        }
    }

    /**
     * Registers a Listener.
     *
     * @param phpbu\App\Listener
     */
    public function addListener(Listener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a Listener.
     *
     * @param phpbu\App\Listener $listener
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
