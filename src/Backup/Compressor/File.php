<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable\Compressor;
use phpbu\App\Result;

/**
 * File
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class File extends Abstraction implements Executable
{
    /**
     * Validate path.
     *
     * @param  string $path
     * @return boolean
     */
    public function isPathValid($path)
    {
        return is_file($path);
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target) {
        if (null === $this->executable) {
            $cmd              = $target->getCompressor()->getCommand();
            $this->executable = new Compressor($cmd, $this->pathToCommand);
            $this->executable->force(true)->compressFile($this->path);
        }
        return $this->executable;
    }
}
