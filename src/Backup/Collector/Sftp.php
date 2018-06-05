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
class Sftp extends Remote implements Collector
{
    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Path   $path
     * @param \phpseclib\Net\SFTP      $sftp
     */
    public function __construct(Target $target, Path $path, \phpseclib\Net\SFTP $sftp)
    {
        $this->setUp($target, $path);
        $this->sftp = $sftp;
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    protected function collectBackups(): array
    {
        $this->collect($this->path->getPathThatIsNotChanging());
        return $this->files;
    }

    /**
     * Collect all remote files in all matching directories.
     *
     * @param string $path
     */
    private function collect(string $path)
    {
        // collect all matching sub directories and get all the backup files
        $depth = Util\Path::getPathDepth($path);
        $list  = $this->sftp->_list($path);
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

    /**
     * Collect matching files in a directory.
     *
     * @param array  $fileList
     * @param string $path
     */
    private function collectFiles(array $fileList, string $path)
    {
        foreach ($fileList as $file) {
            if (in_array($file['filename'], ['.', '..'])) {
                continue;
            }
            if ($this->isFileMatch($path . '/' . $file['filename'])) {
                $file                = new \phpbu\App\Backup\File\Sftp($this->sftp, $file, $path);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
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
