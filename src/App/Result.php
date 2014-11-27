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
     * List of executed Backups
     *
     * @var array<\phpbu\App\Result\Backup>
     */
    protected $backups = array();

    /**
     * Currently running backup.
     *
     * @var \phpbu\App\Result\Backup
     */
    protected $backupActive;

    /**
     * List of errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * @var integer
     */
    protected $backupsFailed = 0;

    /**
     * @var integer
     */
    protected $checksFailed = 0;

    /**
     * @var integer
     */
    protected $syncsSkipped = 0;

    /**
     * @var integer
     */
    protected $syncsFailed = 0;

    /**
     * @var integer
     */
    protected $cleanupsSkipped = 0;

    /**
     * @var integer
     */
    protected $cleanupsFailed = 0;

    /**
     * @return boolean
     */
    public function wasSuccessful()
    {
        return $this->backupsFailed === 0;
    }

    /**
     * @return boolean
     */
    public function noneSkipped()
    {
        return $this->syncsSkipped + $this->cleanupsSkipped === 0;
    }

    /**
     * @return boolean
     */
    public function noneFailed()
    {
        return $this->syncsFailed + $this->cleanupsFailed === 0;
    }

    /**
     * Add Exception to error list
     *
     * @param Exception $e
     */
    public function addError(\Exception $e)
    {
        $this->errors[] = $e;
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
     * Returns list of errors
     *
     * @return array<Exception>
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Return list of executed backups.
     *
     * @return array
     */
    public function getBackups() {
        return $this->backups;
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
        $this->backupActive = new Result\Backup($backup['source']['type']);
        $this->backups[]    = $this->backupActive;

        foreach ($this->listeners as $l) {
            $l->backupStart($backup);
        }
    }

    /**
     * @param array $backup
     */
    public function backupFailed($backup)
    {
        $this->backupsFailed++;
        $this->backupActive->fail();

        foreach ($this->listeners as $l) {
            $l->backupFailed($backup);
        }
    }

    /**
     * Return amount of failed backups
     *
     * @return integer
     */
    public function backupsFailedCount()
    {
        return $this->backupsFailed;
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
    {;
        $this->backupActive->checkAdd($check);
        foreach ($this->listeners as $l) {
            $l->checkStart($check);
        }
    }

    /**
     * @param array $check
     */
    public function checkFailed($check)
    {
        $this->checksFailed++;
        $this->backupActive->fail();
        $this->backupActive->checkFailed($check);
        foreach ($this->listeners as $l) {
            $l->checkFailed($check);
        }
    }

    /**
     * Return amount of failed checks
     *
     * @return integer
     */
    public function checksFailedCount()
    {
        return $this->checksFailed;
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
        $this->backupActive->syncAdd($sync);
        foreach ($this->listeners as $l) {
            $l->syncStart($sync);
        }
    }

    /**
     * @param array $sync
     */
    public function syncSkipped($sync)
    {
        $this->syncsSkipped++;
        $this->backupActive->syncSkipped($sync);
        foreach ($this->listeners as $l) {
            $l->syncSkip($sync);
        }
    }

    /**
     * Return amount of skipped syncs
     *
     * @return integer
     */
    public function syncsSkippedCount()
    {
        return $this->syncsSkipped;
    }

    /**
     * @param array $sync
     */
    public function syncFailed($sync)
    {
        $this->syncsFailed++;
        $this->backupActive->syncFailed($sync);
        foreach ($this->listeners as $l) {
            $l->syncFailed($sync);
        }
    }

    /**
     * Return amount of failed syncs
     *
     * @return integer
     */
    public function syncsFailedCount()
    {
        return $this->syncsFailed;
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
        $this->backupActive->cleanupAdd($cleanup);
        foreach ($this->listeners as $l) {
            $l->cleanupStart($cleanup);
        }
    }

    /**
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup)
    {
        $this->cleanupsSkipped++;
        $this->backupActive->cleanupSkipped($cleanup);
        foreach ($this->listeners as $l) {
            $l->cleanupSkipped($cleanup);
        }
    }

    /**
     * Return amount of skipped cleanups
     *
     * @return integer
     */
    public function cleanupsSkippedCount()
    {
        return $this->cleanupsSkipped;
    }

    /**
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup)
    {
        $this->cleanupsFailed++;
        $this->backupActive->cleanupFailed($cleanup);
        foreach ($this->listeners as $l) {
            $l->cleanupFailed($cleanup);
        }
    }

    /**
     * Return amount of failed cleanups
     *
     * @return integer
     */
    public function cleanupsFailedCount()
    {
        return $this->cleanupsFailed;
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
