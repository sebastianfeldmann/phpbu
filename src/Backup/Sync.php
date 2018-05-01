<?php
namespace phpbu\App\Backup;

use phpbu\App\Backup\File\FileRemote;
use phpbu\App\Result;

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
interface Sync
{
    /**
     * Setup the Sync object with all xml options.
     *
     * @param array $options
     */
    public function setup(array $options);

    /**
     * Execute the Sync
     * Copy your backup to another location
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function sync(Target $target, Result $result);

    /**
     * Execute the remote clean up if needed
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function cleanup(Target $target, Result $result);
}
