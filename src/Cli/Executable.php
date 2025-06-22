<?php
namespace phpbu\App\Cli;

use SebastianFeldmann\Cli\Command;

/**
 * Executable
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
interface Executable extends Command
{
    /**
     * Return the command with masked passwords or keys.
     *
     * @return string
     */
    public function getCommandPrintable();
}
