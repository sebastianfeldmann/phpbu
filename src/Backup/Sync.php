<?php
namespace phpbu\App\Backup;

use phpbu\App\Backup\Sync\Exception;
use phpbu\App\Backup\Sync\Simulator;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
abstract class Sync implements Simulator
{
    /**
     * Setup the Sync object with all xml options.
     *
     * @param array $options
     */
    abstract public function setup(array $options);

    /**
     * Execute the Sync
     * Copy your backup to another location
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    abstract public function sync(Target $target, Result $result);

    /**
     * Make sure all mandatory keys are present in given config.
     *
     * @param  array $config
     * @param  array $keys
     * @throws Exception
     */
    protected function validateConfig(array $config, array $keys)
    {
        foreach ($keys as $option) {
            if (!Util\Arr::isSetAndNotEmptyString($config, $option)) {
                throw new Exception($option . ' is mandatory');
            }
        }
    }
}
