<?php
namespace phpbu\App;

/**
 * Runner result.
 *
 * Heavily 'inspired' by Sebastian Bergmann's phpunit PHPUnit_Framework_TestResult.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Result
{
    /**
     * List of Logging listeners
     *
     * @var array<\phpbu\App\Listener>
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
     * @param \Exception $e
     */
    public function addError(\Exception $e)
    {
        $this->errors[] = $e;
    }

    /**
     * Return current error count.
     *
     * @return integer
     */
    public function errorCount()
    {
        return count($this->errors);
    }

    /**
     * Return list of errors.
     *
     * @return array<Exception>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return list of executed backups.
     *
     * @return array
     */
    public function getBackups()
    {
        return $this->backups;
    }

    /**
     * phpbu start event.
     * 
     * @param array $settings
     */
    public function phpbuStart(array $settings)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->phpbuStart($settings);
        }
    }

    /**
     * phpbu end event.
     */
    public function phpbuEnd()
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->phpbuEnd($this);
        }
    }

    /**
     * Backup start event.
     * 
     * @param array $backup
     */
    public function backupStart($backup)
    {
        $this->backupActive = new Result\Backup(!empty($backup['name']) ? $backup['name'] : $backup['source']['type']);
        $this->backups[]    = $this->backupActive;
        
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->backupStart($backup);
        }
    }

    /**
     * Backup failed event.
     * 
     * @param array $backup
     */
    public function backupFailed($backup)
    {
        $this->backupsFailed++;
        $this->backupActive->fail();

        /** @var \phpbu\App\Listener $l */
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
     * Backup end event.
     * 
     * @param array $backup
     */
    public function backupEnd($backup)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->backupEnd($backup);
        }
    }

    /**
     * Check start event.
     * 
     * @param array $check
     */
    public function checkStart($check)
    {
        $this->backupActive->checkAdd($check);

        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->checkStart($check);
        }
    }

    /**
     * Check failed event.
     * 
     * @param array $check
     */
    public function checkFailed($check)
    {
        $this->checksFailed++;
        $this->backupActive->fail();
        $this->backupActive->checkFailed($check);

        /** @var \phpbu\App\Listener $l */
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
     * Check end event.
     * 
     * @param array $check
     */
    public function checkEnd($check)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->checkEnd($check);
        }
    }

    /**
     * Sync start event.
     * 
     * @param array $sync
     */
    public function syncStart($sync)
    {
        $this->backupActive->syncAdd($sync);
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->syncStart($sync);
        }
    }

    /**
     * Sync skipped event.
     * 
     * @param array $sync
     */
    public function syncSkipped($sync)
    {
        $this->syncsSkipped++;
        $this->backupActive->syncSkipped($sync);
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->syncSkipped($sync);
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
     * Sync failed event.
     * 
     * @param array $sync
     */
    public function syncFailed($sync)
    {
        $this->syncsFailed++;
        $this->backupActive->syncFailed($sync);
        /** @var \phpbu\App\Listener $l */
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
     * Sync end event.
     * 
     * @param array $sync
     */
    public function syncEnd($sync)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->syncEnd($sync);
        }
    }

    /**
     * Cleanup start event.
     * 
     * @param array $cleanup
     */
    public function cleanupStart($cleanup)
    {
        $this->backupActive->cleanupAdd($cleanup);
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->cleanupStart($cleanup);
        }
    }

    /**
     * Cleanup skipped event.
     * 
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup)
    {
        $this->cleanupsSkipped++;
        $this->backupActive->cleanupSkipped($cleanup);
        /** @var \phpbu\App\Listener $l */
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
     * Cleanup failed event.
     * 
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup)
    {
        $this->cleanupsFailed++;
        $this->backupActive->cleanupFailed($cleanup);
        /** @var \phpbu\App\Listener $l */
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
     * Cleanup end event.
     * 
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $l) {
            $l->cleanupEnd($cleanup);
        }
    }

    /**
     * Debug.
     * 
     * @param string $msg
     */
    public function debug($msg)
    {
        /** @var \phpbu\App\Listener $l */
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
     * Remove a Listener.
     *
     * @author Sebastian Bergmann <sebastian@phpunit.de>
     * @param  \phpbu\App\Listener $listener
     */
    public function removeListener(Listener $listener)
    {
        /** @var \phpbu\App\Listener $l */
        foreach ($this->listeners as $key => $l) {
            if ($listener === $l) {
                unset($this->listeners[$key]);
            }
        }
    }
}
