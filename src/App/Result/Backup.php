<?php
namespace phpbu\App\Result;

/**
 * Backup Result
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
    protected $checks = array();

    /**
     * List of failed checks
     *
     * @var array
     */
    protected $checksFailed = array();

    /**
     * List of executed syncs
     *
     * @var array
     */
    protected $syncs = array();

    /**
     * List of skipped syncs
     *
     * @var array
     */
    protected $syncsSkipped = array();

    /**
     * List of failed syncs
     *
     * @var array
     */
    protected $syncsFailed = array();

    /**
     * List of executed cleanups
     *
     * @var array
     */
    protected $cleanups = array();

    /**
     * List of skipped clanup
     *
     * @var array
     */
    protected $cleanupsSkipped = array();

    /**
     * List of failed cleanups
     *
     * @var array
     */
    protected $cleanupsFailed = array();

    /**
     * Constructor
     *
     * @param string $type
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
     * Backup executed successfully and no checks failed
     *
     * @return boolean
     */
    public function wasSuccessful()
    {
        return $this->wasSuccessful;
    }

    /**
     * No skipped syncs or cleanups
     *
     * @return boolean
     */
    public function noneSkipped()
    {
        return count($this->syncsSkipped) + count($this->cleanupsSkipped) === 0;
    }

    /**
     * No failed syncs or cleanups
     *
     * @return boolean
     */
    public function noneFailed()
    {
        return count($this->syncsFailed) + count($this->cleanupsFailed) === 0;
    }


    /**
     * Mark backup as failed
     */
    public function fail()
    {
        $this->wasSuccessful = false;
    }

    /**
     * Add check to executed list
     *
     * @param array $check
     */
    public function checkAdd($check)
    {
        $this->checks[] = $check;
    }

    /**
     * Return amount of executed checks
     *
     * @return number
     */
    public function checkCount()
    {
        return count($this->checks);
    }

    /**
     * Add check to failed checks list
     *
     * @param array $check
     */
    public function checkFailed($check)
    {
        $this->checksFailed[] = $check;
    }

    /**
     * Return amount of failed checks
     *
     * @return number
     */
    public function checkCountFailed()
    {
        return count($this->checksFailed);
    }

    /**
     * Add sync to executed syncs list
     *
     * @param array $sync
     */
    public function syncAdd($sync)
    {
        $this->syncs[] = $sync;
    }

    /**
     * Return count of executed syncs
     *
     * @return number
     */
    public function syncCount()
    {
        return count($this->syncs);
    }

    /**
     * Add sync to skippded syncs list
     *
     * @param array $sync
     */
    public function syncSkipped($sync)
    {
        $this->syncsSkipped[] = $sync;
    }

    /**
     * Return amount of skipped syncs
     *
     * @return number
     */
    public function syncCountSkipped()
    {
        return count($this->syncsSkipped);
    }

    /**
     * Add sync to failed syncs list
     *
     * @param array $sync
     */
    public function syncFailed($sync)
    {
        $this->syncsFailed[] = $sync;
    }

    /**
     * Return amount of failed syncs
     *
     * @return number
     */
    public function syncCountFailed()
    {
        return count($this->syncsFailed);
    }

    /**
     * Add cleanup to executed cleanups list
     *
     * @param array $cleanup
     */
    public function cleanupAdd($cleanup)
    {
        $this->cleanups[] = $cleanup;
    }

    /**
     * Return amount of executed cleanups
     *
     * @return number
     */
    public function cleanupCount()
    {
        return count($this->cleanups);
    }

    /**
     * Add cleanup to skipped cleanups list
     *
     * @param array $cleanup
     */
    public function cleanupSkipped($cleanup)
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
     * Add cleanup to failed cleanups list
     *
     * @param array $cleanup
     */
    public function cleanupFailed($cleanup)
    {
        $this->cleanupsFailed[] = $cleanup;
    }

    /**
     * Return amount of failed cleanups
     *
     * @return number
     */
    public function cleanupCountFailed()
    {
        return count($this->cleanupsFailed);
    }
}
