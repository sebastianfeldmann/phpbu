<?php
namespace phpbu\Backup;

use phpbu\App\Result;
use phpbu\Backup\Target;

/**
 * Source interface
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Source
{
    /**
     * Setup the source.
     *
     * @param  Target $target
     * @param  array  $conf
     */
    public function setup(Target $target, array $conf = array());

    /**
     * Runner the backup
     *
     * @param  Result $result
     * @return Result
     */
    public function backup(Result $result);
}
