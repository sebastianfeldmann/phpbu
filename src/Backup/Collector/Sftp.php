<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

/**
 * Sftp class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Sftp extends Collector
{
    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * SFTP remote path
     *
     * @var Path
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpseclib\Net\SFTP      $sftp
     * @param Path                     $path
     */
    public function __construct(Target $target, \phpseclib\Net\SFTP $sftp, Path $path)
    {
        $this->sftp = $sftp;
        $this->path = $path;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles(): array
    {
        $this->collect($this->path->getPathThatIsNotChanging());
        return $this->files;
    }

    private function collect(string $path)
    {
        // collect all matching sub directories and get all the backup files
        $depth = Util\Path::getPathDepth($path);
        $list = $this->sftp->_list($path);
        if (!$list) {
            return;
        }
        if ($depth < $this->path->getPathDepth()) {
            foreach ($list as $file) {
                if (in_array($file['filename'], ['.', '..'])) {
                    continue;
                }
                if ($this->isValidDirectory($file, $depth)) {
                    $this->collect($path . '/' . $file['filename']);
                }
            }
        } else {
            $this->collectFiles($list, $path);
        }
    }

    private function collectFiles(array $fileList, string $path)
    {
        foreach ($fileList as $file) {
            if (in_array($file['filename'], ['.', '..'])) {
                continue;
            }
            // skip currently created backup
            if ($path . '/' . $file['filename'] == $this->path->getPath() . '/' . $this->target->getFilename()) {
                continue;
            }
            if ($this->isFileMatch($path . '/' . $file['filename'])) {
                $file = new \phpbu\App\Backup\File\Sftp($this->sftp, $file, $path);
                $this->files[$file->getMTime()] = $file;
            }
        }
    }

    /**
     * Check if the iterated file is part of a valid target path.
     *
     * @param array $file
     * @param int   $depth
     * @return bool
     */
    protected function isValidDirectory(array $file, int $depth)
    {
        return $file['type'] == 2 && $this->isMatchingDirectory($file['filename'], $depth);
    }

    /**
     * Does a directory match the respective target path.
     *
     * @param  string $dir
     * @param  int    $depth
     * @return bool
     */
    protected function isMatchingDirectory(string $dir, int $depth)
    {
        $dirTarget = $this->path->getPathElementAtIndex($depth);
        $dirRegex  = Util\Path::datePlaceholdersToRegex($dirTarget);
        return preg_match('#' . $dirRegex . '#i', $dir);
    }
}
