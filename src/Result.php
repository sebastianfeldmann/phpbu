<?php
namespace phpbu\App;

use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Event\Dispatcher;

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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Result
{
    /**
     * EventDispatcher
     *
     * @var \phpbu\App\Event\Dispatcher
     */
    protected $eventDispatcher;

    /**
     * List of executed Backups
     *
     * @var array<\phpbu\App\Result\Backup>
     */
    protected $backups = [];

    /**
     * Currently running backup
     *
     * @var \phpbu\App\Result\Backup
     */
    protected $backupActive;

    /**
     * List of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Start timestamp
     *
     * @var float
     */
    protected $start;

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
    protected $cryptsSkipped = 0;

    /**
     * @var integer
     */
    protected $cryptsFailed = 0;

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
     * Constructor
     */
    public function __construct()
    {
        $this->start           = microtime(true);
        $this->eventDispatcher = new Dispatcher();
    }

    /**
     * Registers a Listener/Subscriber
     *
     * @param \phpbu\App\Listener $subscriber
     */
    public function addListener(Listener $subscriber) : void
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * No errors at all?
     *
     * @return bool
     */
    public function allOk() : bool
    {
        return $this->wasSuccessful() && $this->noneSkipped() && $this->noneFailed();
    }

    /**
     * Backup without errors, but some tasks where skipped or failed.
     *
     * @return bool
     */
    public function backupOkButSkipsOrFails() : bool
    {
        return $this->wasSuccessful() && (!$this->noneSkipped() || !$this->noneFailed());
    }

    /**
     * Backup without errors?
     *
     * @return bool
     */
    public function wasSuccessful() : bool
    {
        return $this->backupsFailed === 0;
    }

    /**
     * Nothing skipped?
     *
     * @return bool
     */
    public function noneSkipped() : bool
    {
        return $this->cryptsSkipped + $this->syncsSkipped + $this->cleanupsSkipped === 0;
    }

    /**
     * Nothing failed?
     *
     * @return bool
     */
    public function noneFailed() : bool
    {
        return $this->checksFailed + $this->cryptsFailed + $this->syncsFailed + $this->cleanupsFailed === 0;
    }

    /**
     * Add Exception to error list
     *
     * @param \Exception $e
     */
    public function addError(\Exception $e) : void
    {
        $this->errors[] = $e;
    }

    /**
     * Return current error count
     *
     * @return int
     */
    public function errorCount() : int
    {
        return count($this->errors);
    }

    /**
     * Return list of errors
     *
     * @return array<Exception>
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * Return list of executed backups
     *
     * @return array<\phpbu\App\Result\Backup>
     */
    public function getBackups() : array
    {
        return $this->backups;
    }

    /**
     * phpbu start event
     *
     * @param \phpbu\App\Configuration $configuration
     */
    public function phpbuStart(Configuration $configuration) : void
    {
        $event = new Event\App\Start($configuration);
        $this->eventDispatcher->dispatch(Event\App\Start::NAME, $event);
    }

    /**
     * phpbu end event.
     */
    public function phpbuEnd() : void
    {
        $event = new Event\App\End($this);
        $this->eventDispatcher->dispatch(Event\App\End::NAME, $event);
    }

    /**
     * Return phpbu start micro time
     *
     * @return float
     */
    public function started() : float
    {
        return $this->start;
    }

    /**
     * Backup start event
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Source $source
     * @throws \phpbu\App\Exception
     */
    public function backupStart(Configuration\Backup $backup, Target $target, Source $source) : void
    {
        $this->backupActive = new Result\Backup($backup->getName());
        $this->backups[]    = $this->backupActive;

        $event = new Event\Backup\Start($backup, $target, $source);
        $this->eventDispatcher->dispatch(Event\Backup\Start::NAME, $event);
    }

    /**
     * Backup failed event
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Source $source
     */
    public function backupFailed(Configuration\Backup $backup, Target $target, Source $source) : void
    {
        $this->backupsFailed++;
        $this->backupActive->fail();

        $event = new Event\Backup\Failed($backup, $target, $source);
        $this->eventDispatcher->dispatch(Event\Backup\Failed::NAME, $event);
    }

    /**
     * Return amount of failed backups
     *
     * @return int
     */
    public function backupsFailedCount() : int
    {
        return $this->backupsFailed;
    }

    /**
     * Backup end event
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Source $source
     */
    public function backupEnd(Configuration\Backup $backup, Target $target, Source $source) : void
    {
        $event = new Event\Backup\End($backup, $target, $source);
        $this->eventDispatcher->dispatch(Event\Backup\End::NAME, $event);
    }

    /**
     * Check start event
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function checkStart(Configuration\Backup\Check $check) : void
    {
        $this->backupActive->checkAdd($check);

        $event = new Event\Check\Start($check);
        $this->eventDispatcher->dispatch(Event\Check\Start::NAME, $event);
    }

    /**
     * Check failed event
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function checkFailed(Configuration\Backup\Check $check) : void
    {
        // if this is the first check that fails
        if ($this->backupActive->wasSuccessful()) {
            $this->backupsFailed++;
        }

        $this->checksFailed++;
        $this->backupActive->fail();
        $this->backupActive->checkFailed($check);

        $event = new Event\Check\Failed($check);
        $this->eventDispatcher->dispatch(Event\Check\Failed::NAME, $event);
    }

    /**
     * Return amount of failed checks
     *
     * @return int
     */
    public function checksFailedCount() : int
    {
        return $this->checksFailed;
    }

    /**
     * Check end event
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function checkEnd(Configuration\Backup\Check $check) : void
    {
        $event = new Event\Check\End($check);
        $this->eventDispatcher->dispatch(Event\Check\End::NAME, $event);
    }

    /**
     * Crypt start event
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptStart(Configuration\Backup\Crypt $crypt) : void
    {
        $this->backupActive->cryptAdd($crypt);

        $event = new Event\Crypt\Start($crypt);
        $this->eventDispatcher->dispatch(Event\Crypt\Start::NAME, $event);
    }

    /**
     * Crypt skipped event
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptSkipped(Configuration\Backup\Crypt $crypt) : void
    {
        $this->cryptsSkipped++;
        $this->backupActive->cryptSkipped($crypt);

        $event = new Event\Crypt\Skipped($crypt);
        $this->eventDispatcher->dispatch(Event\Crypt\Skipped::NAME, $event);
    }

    /**
     * Return amount of skipped crypts
     *
     * @return int
     */
    public function cryptsSkippedCount() : int
    {
        return $this->cryptsSkipped;
    }

    /**
     * Crypt failed event
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptFailed(Configuration\Backup\Crypt $crypt) : void
    {
        $this->cryptsFailed++;
        $this->backupActive->fail();
        $this->backupActive->cryptFailed($crypt);

        $event = new Event\Crypt\Failed($crypt);
        $this->eventDispatcher->dispatch(Event\Crypt\Failed::NAME, $event);
    }

    /**
     * Return amount of failed crypts
     *
     * @return int
     */
    public function cryptsFailedCount() : int
    {
        return $this->cryptsFailed;
    }

    /**
     * Crypt end event
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptEnd(Configuration\Backup\Crypt $crypt) : void
    {
        $event = new Event\Crypt\End($crypt);
        $this->eventDispatcher->dispatch(Event\Crypt\End::NAME, $event);
    }

    /**
     * Sync start event
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncStart(Configuration\Backup\Sync $sync) : void
    {
        $this->backupActive->syncAdd($sync);

        $event = new Event\Sync\Start($sync);
        $this->eventDispatcher->dispatch(Event\Sync\Start::NAME, $event);
    }

    /**
     * Sync skipped event
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncSkipped(Configuration\Backup\Sync $sync) : void
    {
        $this->syncsSkipped++;
        $this->backupActive->syncSkipped($sync);

        $event = new Event\Sync\Skipped($sync);
        $this->eventDispatcher->dispatch(Event\Sync\Skipped::NAME, $event);
    }

    /**
     * Return amount of skipped syncs
     *
     * @return int
     */
    public function syncsSkippedCount() : int
    {
        return $this->syncsSkipped;
    }

    /**
     * Sync failed event
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncFailed(Configuration\Backup\Sync $sync) : void
    {
        $this->syncsFailed++;
        $this->backupActive->syncFailed($sync);

        $event = new Event\Sync\Failed($sync);
        $this->eventDispatcher->dispatch(Event\Sync\Failed::NAME, $event);
    }

    /**
     * Return amount of failed syncs
     *
     * @return int
     */
    public function syncsFailedCount() : int
    {
        return $this->syncsFailed;
    }

    /**
     * Sync end event
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncEnd(Configuration\Backup\Sync $sync) : void
    {
        $event = new Event\Sync\End($sync);
        $this->eventDispatcher->dispatch(Event\Sync\End::NAME, $event);
    }

    /**
     * Cleanup start event
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupStart(Configuration\Backup\Cleanup $cleanup) : void
    {
        $this->backupActive->cleanupAdd($cleanup);

        $event = new Event\Cleanup\Start($cleanup);
        $this->eventDispatcher->dispatch(Event\Cleanup\Start::NAME, $event);
    }

    /**
     * Cleanup skipped event
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupSkipped(Configuration\Backup\Cleanup $cleanup) : void
    {
        $this->cleanupsSkipped++;
        $this->backupActive->cleanupSkipped($cleanup);

        $event = new Event\Cleanup\Skipped($cleanup);
        $this->eventDispatcher->dispatch(Event\Cleanup\Skipped::NAME, $event);
    }

    /**
     * Return amount of skipped cleanups
     *
     * @return int
     */
    public function cleanupsSkippedCount() : int
    {
        return $this->cleanupsSkipped;
    }

    /**
     * Cleanup failed event
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupFailed(Configuration\Backup\Cleanup $cleanup) : void
    {
        $this->cleanupsFailed++;
        $this->backupActive->cleanupFailed($cleanup);

        $event = new Event\Cleanup\Failed($cleanup);
        $this->eventDispatcher->dispatch(Event\Cleanup\Failed::NAME, $event);
    }

    /**
     * Return amount of failed cleanups
     *
     * @return int
     */
    public function cleanupsFailedCount() : int
    {
        return $this->cleanupsFailed;
    }

    /**
     * Cleanup end event
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupEnd(Configuration\Backup\Cleanup $cleanup) : void
    {
        $event = new Event\Cleanup\End($cleanup);
        $this->eventDispatcher->dispatch(Event\Cleanup\End::NAME, $event);
    }

    /**
     * Debug
     *
     * @param string $msg
     */
    public function debug($msg) : void
    {
        $event = new Event\Debug($msg);
        $this->eventDispatcher->dispatch(Event\Debug::NAME, $event);
    }

    /**
     * Warning
     *
     * @param string $msg
     */
    public function warn($msg) : void
    {
        $event = new Event\Warning($msg);
        $this->eventDispatcher->dispatch(Event\Warning::NAME, $event);
    }
}
