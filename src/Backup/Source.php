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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
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
    public function setup(array $conf = []);

    /**
     * Execute the backup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     */
    public function backup(Target $target, Result $result);
}
