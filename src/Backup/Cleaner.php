<?php
namespace phpbu\App\Backup;

use phpbu\App\Result;

/**
 * Cleanup
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Cleaner
{
    /**
     * Setup the cleaner.
     *
     * @param  array $options
     * @return void
     */
    public function setup(array $options);

    /**
     * Cleanup you backup location
     *
     * @param  \phpbu\App\Backup\Target          $target
     * @param  \phpbu\App\Backup\Collector       $collector
     * @param  \phpbu\App\Result                 $result
     * @return void
     */
    public function cleanup(Target $target, Collector $collector, Result $result);
}
