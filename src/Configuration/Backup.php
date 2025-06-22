<?php
namespace phpbu\App\Configuration;

use phpbu\App\Exception;

/**
 * Backup
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Backup
{
    /**
     * Backup name
     *
     * @var string
     */
    private $name;

    /**
     * Stop all other backups on failure
     *
     * @var boolean
     */
    private $stopOnFailure;

    /**
     * Source configuration
     *
     * @var \phpbu\App\Configuration\Backup\Source
     */
    private $source;

    /**
     * Target configuration
     *
     * @var \phpbu\App\Configuration\Backup\Target
     */
    private $target;

    /**
     * List of configured Checks
     *
     * @var array<\phpbu\App\Configuration\Backup\Check>
     */
    private $checks = [];

    /**
     * Crypt configuration
     *
     * @var \phpbu\App\Configuration\Backup\Crypt
     */
    private $crypt;

    /**
     * List of configured Syncs
     *
     * @var array<\phpbu\App\Configuration\Backup\Sync>
     */
    private $syncs = [];

    /**
     * Cleanup configuration
     *
     * @var \phpbu\App\Configuration\Backup\Cleanup
     */
    private $cleanup;

    /**
     * Constructor
     *
     * @param string  $name
     * @param boolean $stopOnFailure
     */
    public function __construct($name, $stopOnFailure)
    {
        $this->name          = $name;
        $this->stopOnFailure = $stopOnFailure;
    }

    /**
     * Returns name for the backup.
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        if (!empty($this->source)) {
            return $this->source->type;
        }
        throw new Exception('no name and no source');
    }

    /**
     * StopOnFailure getter.
     *
     * @return boolean
     */
    public function stopOnFailure()
    {
        return $this->stopOnFailure;
    }

    /**
     * Data source setter.
     *
     * @param \phpbu\App\Configuration\Backup\Source $source
     */
    public function setSource(Backup\Source $source)
    {
        $this->source = $source;
    }

    /**
     * Source getter.
     *
     * @return \phpbu\App\Configuration\Backup\Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Target setter.
     *
     * @param \phpbu\App\Configuration\Backup\Target $target
     */
    public function setTarget(Backup\Target $target)
    {
        $this->target = $target;
    }

    /**
     * Target getter.
     *
     * @return \phpbu\App\Configuration\Backup\Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Adds a check to the list.
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function addCheck(Backup\Check $check)
    {
        $this->checks[] = $check;
    }

    /**
     * Returns list of checks.
     *
     * @return array<\phpbu\App\Configuration\Backup\Check>
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * Crypt setter.
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function setCrypt(Backup\Crypt $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Crypt getter.
     *
     * @return \phpbu\App\Configuration\Backup\Crypt
     */
    public function getCrypt()
    {
        return $this->crypt;
    }

    /**
     * Is crypt set.
     *
     * @return bool
     */
    public function hasCrypt()
    {
        return !empty($this->crypt);
    }

    /**
     * Add sync to list.
     *
     * @param \phpbu\App\Configuration\Backup\Sync $sync
     */
    public function addSync(Backup\Sync $sync)
    {
        $this->syncs[] = $sync;
    }

    /**
     * Returns list of syncs.
     *
     * @return array<\phpbu\App\Configuration\Backup\Sync>
     */
    public function getSyncs()
    {
        return $this->syncs;
    }

    /**
     * Cleanup setter.
     *
     * @param \phpbu\App\Configuration\Backup\Cleanup $cleanup
     */
    public function setCleanup(Backup\Cleanup $cleanup)
    {
        $this->cleanup = $cleanup;
    }

    /**
     * Cleanup getter.
     *
     * @return \phpbu\App\Configuration\Backup\Cleanup
     */
    public function getCleanup()
    {
        return $this->cleanup;
    }

    /**
     * Is cleanup set.
     *
     * @return bool
     */
    public function hasCleanup()
    {
        return !empty($this->cleanup);
    }
}
