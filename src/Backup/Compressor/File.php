<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Executable\Compressor;
use phpbu\App\Exception;
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
class File extends Abstraction
{
    /**
     * Validate path.
     *
     * @param  string $path
     * @return boolean
     */
    public function isPathValid($path) : bool
    {
        return is_file($path);
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        if (!$target->shouldBeCompressed()) {
            throw new Exception('target should not be compressed at all');
        }
        $executable = new Compressor($target->getCompression()->getCommand(), $this->pathToCommand);
        $executable->force(true)->compressFile($this->path);
        return $executable;
    }


    /**
     * Return final archive file.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getArchiveFile(Target $target) : string
    {
        return $target->getPathname();
    }
}
