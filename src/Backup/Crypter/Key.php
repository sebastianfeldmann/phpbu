<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Abstract key crypter class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.6
 */
abstract class Key extends Cli
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

        $result->debug($name . ':' . $crypt->getCmd());

        if (!$crypt->wasSuccessful()) {
            throw new Exception($name . ' failed:' . PHP_EOL . $crypt->getStdErr());
        }
    }

    /**
     * Return an absolute path relative to the used file.
     *
     * @param  string $path
     * @param  string $default
     * @return string
     */
    protected function toAbsolutePath($path, $default = null)
    {
        return !empty($path) ? Util\Cli::toAbsolutePath($path, Util\Cli::getBase('configuration')) : $default;
    }
}
