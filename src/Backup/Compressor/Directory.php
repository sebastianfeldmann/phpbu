<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Executable\Tar;
use phpbu\App\Exception;
use phpbu\App\Result;

/**
 * Directory
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.1
 */
class Directory extends Abstraction
{
    /**
     * Validate path.
     *
     * @param  string $path
     * @return bool
     */
    public function isPathValid($path) : bool
    {
        return is_dir($path);
    }

    /**
     * If 'tar' can't compress with the requested compressor this will return false.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return bool
     * @throws \phpbu\App\Exception
     */
    public function canCompress(Target $target) : bool
    {
        if (!$target->shouldBeCompressed()) {
            throw new Exception('target should not be compressed at all');
        }
        return Tar::isCompressionValid($target->getCompression()->getCommand());
    }

    /**
     * Compress the configured directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function compress(Target $target, Result $result) : string
    {
        $target->setMimeType('application/x-tar');
        $target->appendFileSuffix('tar');
        return parent::compress($target, $result);
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Tar($this->pathToCommand);
        $executable->archiveDirectory($this->path);
        $executable->archiveTo($this->getArchiveFile($target))
                   ->useCompression(
                       $target->shouldBeCompressed() ? $target->getCompression()->getCommand() : ''
                   )
                   ->removeSourceDirectory(true);
        return $executable;
    }

    /**
     * Get final archive name.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getArchiveFile(Target $target) : string
    {
        return $target->shouldBeCompressed() && $this->canCompress($target)
               ? $target->getPathname()
               : $target->getPathnamePlain();
    }
}
