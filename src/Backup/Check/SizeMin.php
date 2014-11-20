<?php
namespace phpbu\Backup\Check;

use phpbu\App\Result;
use phpbu\Backup\Check;
use phpbu\Backup\Target;
use phpbu\Util\String;
use RuntimeException;

/**
 * Backup Target class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeMin implements Check
{
    /**
     * @see \phpbu\Backup\Check::pass()
     */
    public function pass(Target $target, $value, Result $result)
    {
        $file = (string) $target;

        if (!file_exists($file)) {
            throw new RuntimeException('Backup file does not exist');
        }

        $actualSize = filesize($file);
        $testSize   = String::toBytes($value);

        return $testSize <= $actualSize;
    }
}
