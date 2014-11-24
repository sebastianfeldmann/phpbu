<?php
namespace phpbu\Backup;

use phpbu\App\Result;
use phpbu\Backup\Target;

/**
 * Cleanup
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Cleaner
{
    /**
     * Setup the cleaner.
     *
     * @param array $options
     */
    public function setup(array $options);

    /**
     * Cleanup you backup location
     *
     * @param \phpbu\Backup\Target $target
     * @param \phpbu\App\Result    $result;
     */
    public function cleanup(Target $target, Result $result);
}
