<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target;
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
     * @return boolean
     */
    public function isPathValid($path)
    {
        return is_dir($path);
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
        $target->setMimeType('application/x-tar');
        parent::compress($target, $result);
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target) {
        if (null === $this->executable) {
            $this->executable = new Tar($this->pathToCommand);
            $this->executable->archiveDirectory($this->path);

            $archiveName = Tar::isCompressorValid($target->getCompressor()->getCommand())
                         ? $target->getPathname()
                         : $target->getPathnamePlain();
            $this->executable->archiveTo($archiveName)
                             ->useCompression($target->getCompressor()->getCommand())
                             ->removeSourceDirectory(true);
        }
        return $this->executable;
    }
}
