<?php
namespace phpbu\App\Backup\Cleaner\Stepwise;

use phpbu\App\Backup\File;

/**
 * Keeper interface.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
interface Keeper
{
    /**
     * Decides if given file should be kept.
     *
     * @param  \phpbu\App\Backup\File $file
     * @return bool
     */
    public function keep(File $file) : bool;
}
