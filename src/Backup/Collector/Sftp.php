<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

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
     * OpenStack remote path
     *
     * @var string
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpseclib\Net\SFTP      $sftp
     * @param string                   $path
     */
    public function __construct(Target $target, \phpseclib\Net\SFTP $sftp, string $path)
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
        $list = $this->sftp->_list($this->path);
        foreach ($list as $fileInfo) {
            if ($fileInfo['type'] === 2) {
                continue;
            }
            // skip currently created backup
            if ($fileInfo['filename'] == $this->target->getFilename()) {
                continue;
            }
            if ($this->isFilenameMatch($fileInfo['filename'])) {
                $this->files[] = new \phpbu\App\Backup\File\Sftp($this->sftp, $fileInfo, $this->path);
            }
        }

        return $this->files;
    }
}
