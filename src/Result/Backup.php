<?php
namespace phpbu\App\Result;

use phpbu\App\Configuration;

/**
 * Backup Result
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Backup
{
    /**
     * Backup name
     *
     * @var string
     */
    protected $name;

    /**
     * Backup was successful
     *
     * @var boolean
     */
    protected $wasSuccessful = true;

    /**
     * List of executed checks
     *
     * @var array
     */
    protected $checks = [];

    /**
     * List of failed checks
     *
     * @var array
     */
    protected $checksFailed = [];

    /**
     * List of executed crypts
     *
     * @var array
     */
    protected $crypts = [];

    /**
     * List of skipped crypts
     *
     * @var array
     */
    protected $cryptsSkipped = [];

    /**
     * List of failed crypts
     *
     * @var array
     */
    protected $cryptsFailed = [];

    /**
     * List of executed syncs
     *
     * @var array
     */
    protected $syncs = [];

    /**
     * List of skipped syncs
     *
     * @var array
     */
    protected $syncsSkipped = [];

    /**
     * List of failed syncs
     *
     * @var array
     */
    protected $syncsFailed = [];

    /**
     * List of executed cleanups
     *
     * @var array
     */
    protected $cleanups = [];

    /**
     * List of skipped cleanups
     *
     * @var array
     */
    protected $cleanupsSkipped = [];

    /**
     * List of failed cleanups
     *
     * @var array
     */
    protected $cleanupsFailed = [];

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Type getter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Backup successful and nothing skipped or failed.
     *
     * @return boolean
     */
    public function allOk()
    {
        return $this->wasSuccessful() && $this->noneSkipped() && $this->noneFailed();
    }

    /**
     * Backup successful but something was skipped or failed.
     *
     * @return boolean
     */
    public function okButSkipsOrFails()
    {
        return $this->wasSuccessful() && (!$this->noneFailed() || !$this->noneSkipped());
    }

    /**
     * Backup executed successfully and no checks failed.
     *
     * @return boolean
     */
    public function wasSuccessful()
    {
        return $this->wasSuccessful;
    }


    /**
     * No skipped crypts, syncs or cleanups.
     *
     * @return boolean
     */
    public function noneSkipped()
    {
        return count($this->cryptsFailed) + count($this->syncsSkipped) + count($this->cleanupsSkipped) === 0;
    }

    /**
     * No failed crypts, syncs or cleanups.
     *
     * @return boolean
     */
    public function noneFailed()
    {
        return count($this->cryptsFailed) + count($this->syncsFailed) + count($this->cleanupsFailed) === 0;
    }


    /**
     * Mark backup as failed.
     */
    public function fail()
    {
        $this->wasSuccessful = false;
    }

    /**
     * Add check to executed list.
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function checkAdd(Configuration\Backup\Check $check)
    {
        $this->checks[] = $check;
    }

    /**
     * Return amount of executed checks.
     *
     * @return number
     */
    public function checkCount()
    {
        return count($this->checks);
    }

    /**
     * Add check to failed checks list.
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function checkFailed(Configuration\Backup\Check$check)
    {
        $this->checksFailed[] = $check;
    }

    /**
     * Return amount of failed checks.
     *
     * @return number
     */
    public function checkCountFailed()
    {
        return count($this->checksFailed);
    }

    /**
     * Add crypt to executed list.
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptAdd(Configuration\Backup\Crypt $crypt)
    {
        $this->crypts[] = $crypt;
    }

    /**
     * Return amount of executed crypts.
     *
     * @return number
     */
    public function cryptCount()
    {
        return count($this->crypts);
    }

    /**
     * Add crypt to skipped syncs list.
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptSkipped(Configuration\Backup\Crypt $crypt)
    {
        $this->cryptsSkipped[] = $crypt;
    }

    /**
     * Return amount of failed crypts.
     *
     * @return number
     */
    public function cryptCountSkipped()
    {
        return count($this->cryptsSkipped);
    }

    /**
     * Add crypt to failed crypts list.
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function cryptFailed(Configuration\Backup\Crypt $crypt)
    {
        $this->cryptsFailed[] = $crypt;
    }

    /**
     * Return amount of failed crypts.
     *
     * @return number
     */
    public function cryptCountFailed()
    {
        return count($this->cryptsFailed);
    }

    /**
     * Add sync to executed syncs list.
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncAdd(Configuration\Backup\Sync $sync)
    {
        $this->syncs[] = $sync;
    }

    /**
     * Return count of executed syncs.
     *
     * @return number
     */
    public function syncCount()
    {
        return count($this->syncs);
    }

    /**
     * Add sync to skipped syncs list.
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncSkipped(Configuration\Backup\Sync $sync)
    {
        $this->syncsSkipped[] = $sync;
    }

    /**
     * Return amount of skipped syncs.
     *
     * @return number
     */
    public function syncCountSkipped()
    {
        return count($this->syncsSkipped);
    }

    /**
     * Add sync to failed syncs list.
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function syncFailed(Configuration\Backup\Sync $sync)
    {
        $this->syncsFailed[] = $sync;
    }

    /**
     * Return amount of failed syncs.
     *
     * @return number
     */
    public function syncCountFailed()
    {
        return count($this->syncsFailed);
    }

    /**
     * Add cleanup to executed cleanups list.
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupAdd(Configuration\Backup\Cleanup $cleanup)
    {
        $this->cleanups[] = $cleanup;
    }

    /**
     * Return amount of executed cleanups.
     *
     * @return number
     */
    public function cleanupCount()
    {
        return count($this->cleanups);
    }

    /**
     * Add cleanup to skipped cleanups list.
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupSkipped(Configuration\Backup\Cleanup $cleanup)
    {
        $this->cleanupsSkipped[] = $cleanup;
    }

    /**
     * Return amount of skipped cleanups
     *
     * @return number
     */
    public function cleanupCountSkipped()
    {
        return count($this->cleanupsSkipped);
    }

    /**
     * Add cleanup to failed cleanups list.
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function cleanupFailed(Configuration\Backup\Cleanup $cleanup)
    {
        $this->cleanupsFailed[] = $cleanup;
    }

    /**
     * Return amount of failed cleanups.
     *
     * @return number
     */
    public function cleanupCountFailed()
    {
        return count($this->cleanupsFailed);
    }
}
