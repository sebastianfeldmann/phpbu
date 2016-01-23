<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Crypter Simulator interface.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
interface Simulator extends Crypter
{
    /**
     * Simulate the encryption execution.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Result           $result
     */
    public function simulate(Target $target, Result $result);
}
