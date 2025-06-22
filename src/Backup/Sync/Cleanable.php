<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration\Backup\Cleanup;
use phpbu\App\Factory;
use phpbu\App\Result;

/**
 * Clearable trait
 *
 * @package    phpbu
 * @subpackage Sync
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
trait Cleanable
{
    /**
     * Cleaner configuration.
     *
     * @var \phpbu\App\Configuration\Backup\Cleanup
     */
    protected $cleanupConfig;

    /**
     * The cleaner instance executing the actual cleaning process.
     *
     * @var \phpbu\App\Backup\Cleaner
     */
    protected $cleaner;

    /**
     * Simulation indicator.
     *
     * @var bool
     */
    protected $isSimulation = false;

    /**
     * Check sync clean configuration entities and set up a proper cleaner
     *
     * @param  array $options
     * @throws \phpbu\App\Exception
     */
    public function setUpCleanable(array $options)
    {
        $config = [];
        foreach ($options as $key => $value) {
            if (strpos($key, "cleanup.") === 0) {
                $config[str_replace('cleanup.', '', $key)] = $value;
            }
        }

        if (isset($config['type'])) {
            $this->cleanupConfig = new Cleanup($config['type'], false, $config);
            $this->cleaner       = (new Factory())->createCleaner(
                $this->cleanupConfig->type,
                $this->cleanupConfig->options
            );
        }
    }

    /**
     * Creates collector for remote cleanup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    abstract protected function createCollector(Target $target): Collector;

    /**
     * Execute the remote clean up if needed.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function cleanup(Target $target, Result $result)
    {
        if (!$this->cleaner) {
            return;
        }

        $collector = $this->createCollector($target);
        $result->debug(sprintf('remote cleanup: %s ', $this->cleanupConfig->type));
        $this->cleaner->cleanup($target, $collector, $result);
    }

    /**
     * Simulate remote cleanup.
     *
     * @param Target $target
     * @param Result $result
     */
    public function simulateRemoteCleanup(Target $target, Result $result)
    {
        if ($this->cleaner) {
            $result->debug(sprintf('remote cleanup: %s ', $this->cleanupConfig->type));
        }
    }
}
