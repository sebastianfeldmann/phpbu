<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Target;
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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.2
 */
class File extends Abstraction
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
     * Compress the configured directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    public function compress(Target $target, Result $result)
    {
        if (!$target->shouldBeCompressed()) {
            throw new Exception('target should not be compressed');
        }

        $res = $this->execute($target);
        $result->debug($res->getCmd());

        if (0 !== $res->getCode()) {
            throw new Exception('Failed to ' . $target->getCompressor()->getCommand() . ' \'compress\' file: ' . $this->path);
        }
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
