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
    protected $listener = array();

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
    protected $sanityFailed = false;

    /**
     * @var boolean
     */
    protected $syncFailed = false;

    /**
     * @param Backup $backup
     */
    public function backupStart($backup)
    {
        foreach($this->listener as $log) {
            $log->info('backup started');
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupFailed($backup)
    {
        $this->backupFailed = true;
        foreach($this->listener as $log) {
            $log->error('backup started');
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityStart($sanity)
    {
        foreach($this->listener as $log) {
            $log->info('sanity start');
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityFailed($sanity)
    {
        $this->sanityFailed = true;
        $call               = $sanity->shouldStopOnFail() ? 'error' : 'warning';
        foreach($this->listener as $log) {
            $log->$call('sanity failed');
        }
    }

    /**
     * @param Sanity $sanity
     */
    public function sanityEnd($sanity)
    {
        foreach($this->listener as $log) {
            $log->$call('sanity done');
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncStart($sync)
    {
        foreach($this->listener as $log) {
            $log->info('sync start');
        }
    }

    /**
     * @param Sync $sync
     */
    public function syncFailed($sync)
    {
        $this->syncFailed = true;
        $call             = $sync->shouldStopOnFail() ? 'error' : 'warning';
        foreach($this->listener as $log) {
            $log->$call('sync failed');
        }
    }

    /**
     * @param Sysc $sync
     */
    public function syncEnd($sync)
    {
        foreach($this->listener as $log)
        {
            $log->info('sync done');
        }
    }

    /**
     * @param Backup $backup
     */
    public function backupEnd($backup)
    {
        foreach($this->listener as $log) {
            $log->info('backup done');
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