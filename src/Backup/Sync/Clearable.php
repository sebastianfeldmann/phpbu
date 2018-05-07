<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration\Backup\Cleanup;
use phpbu\App\Factory;
use phpbu\App\Result;

trait Clearable
{
    /**
     * @var Cleanup
     */
    protected $cleanupConfig;

    /**
     * @var Cleaner
     */
    protected $cleaner;

    /**
     * Check sync clean configuration entities and set up a proper cleaner
     *
     * @param array $options
     * @throws \phpbu\App\Exception
     */
    public function setUpClearable(array $options)
    {
        $config = [];
        foreach ($options as $key => $value) {
            if (strpos($key, "cleanup.") === 0) {
                $config[str_replace('cleanup.', '', $key)] = $value;
            }
        }

        if (isset($config['type'])) {
            $skip = isset($config['skipOnFailure']) ? (bool) $config['skipOnFailure'] : true;
            // creating cleanup config
            $this->cleanupConfig = new Cleanup($config['type'], $skip, $config);
            // creating cleaner
            $this->cleaner = (new Factory())->createCleaner($this->cleanupConfig->type, $this->cleanupConfig->options);
        }
    }

    /**
     * @param Target $target
     * @param Result $result
     */
    public function simulateRemoteCleanup(Target $target, Result $result)
    {
        if ($this->cleaner) {
            $result->debug("  sync cleanup: {$this->cleanupConfig->type}" . PHP_EOL);
        }
    }
}