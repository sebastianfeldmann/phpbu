<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use SebastianFeldmann\Ftp\Client;

/**
 * Ftp class.
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
class Ftp extends Remote implements Collector
{
    /**
     * FTP connection stream
     *
     * @var \SebastianFeldmann\Ftp\Client
     */
    private $ftpClient;

    /**
     * Ftp constructor.
     *
     * @param \phpbu\App\Backup\Target      $target
     * @param \phpbu\App\Backup\Path        $remotePath
     * @param \SebastianFeldmann\Ftp\Client $ftpClient
     */
    public function __construct(Target $target, Path $remotePath, Client $ftpClient)
    {
        $this->setUp($target, $remotePath);
        $this->ftpClient = $ftpClient;
    }

    /**
     * Collect all created backups.
     *
     * @throws \Exception
     */
    protected function collectBackups()
    {
        $this->ftpClient->chHome();

        $initialDepth = $this->path->getPathThatIsNotChangingDepth();
        $initialPath  = $this->path->getPathThatIsNotChanging();

        $this->collect($initialPath, $initialDepth);
    }

    /**
     * Collect all synced backup files regarding to the remote path configuration.
     *
     * @param  string $remotePath
     * @param  int    $depth
     * @throws \Exception
     */
    private function collect(string $remotePath, int $depth)
    {
        if ($depth < $this->path->getPathDepth()) {
            $this->collectDirectories($remotePath, $depth);
            return;
        }
        $this->collectFiles($remotePath);
    }

    /**
     * If path not fully satisfied look for matching directories.
     *
     * @param  string $remotePath
     * @param  int    $depth
     * @return void
     * @throws \Exception
     */
    private function collectDirectories(string $remotePath, int $depth)
    {
        /** @var \SebastianFeldmann\Ftp\File $ftpDir */
        foreach ($this->ftpClient->lsDirs($remotePath) as $ftpDir) {
            $element  = $this->path->getPathElementAtIndex($depth);
            $expected = '#' . Util\Path::datePlaceholdersToRegex($element) . '#i';
            if (\preg_match($expected, $ftpDir->getFilename())) {
                // look for files in a "deeper"  directory
                $this->collect($remotePath . '/' . $ftpDir->getFilename(), $depth + 1);
            }
        }
    }

    /**
     * Collect all matching files in a given remote directory.
     *
     * @param  string $remotePath
     * @return void
     * @throws \Exception
     */
    private function collectFiles(string $remotePath)
    {
        foreach ($this->ftpClient->lsFiles($remotePath) as $ftpFile) {
            if ($this->isFilenameMatch($ftpFile->getFilename())) {
                $file                = new File\Ftp($this->ftpClient, $ftpFile, $remotePath);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }
    }
}
