<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Abstract crypter class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.6
 */
abstract class Abstraction extends Cli
{
    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Crypter
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws Exception
     */
    public function crypt(Target $target, Result $result)
    {
        $crypt = $this->execute($target);
        $name  = strtolower(get_class($this));

        $result->debug($name . ':' . $this->getExecutable($target)->getCommandPrintable());

        if (!$crypt->isSuccessful()) {
            throw new Exception($name . ' failed:' . PHP_EOL . $crypt->getStdErr());
        }
    }

    /**
     * Simulate the encryption.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'execute encryption:' . PHP_EOL .
            $this->getExecutable($target)->getCommandPrintable()
        );
    }
}
