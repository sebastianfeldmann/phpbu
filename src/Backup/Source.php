<?php
namespace phpbu\App\Backup;

use phpbu\App\Result;

/**
 * Source interface
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Source
{
    /**
     * Setup the source.
     *
     * @param array  $conf
     */
    public function setup(array $conf = array());

    /**
     * Runner the backup
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     */
    public function backup(Target $target, Result $result);
}
