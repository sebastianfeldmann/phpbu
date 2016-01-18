<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Tar source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Tar extends Cli implements Source
{
    /**
     * Tar Executable
     *
     * @var \phpbu\App\Cli\Executable\Tar
     */
    protected $executable;

    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToTar;

    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

    /**
     * Path to backup
     *
     * @var string
     */
    private $path;

    /**
     * Remove the packed data
     *
     * @var boolean
     */
    private $removeDir;

    /**
     * Compression to use.
     *
     * @var string
     */
    private $compression;

    /**
     * Path where to store the archive.
     *
     * @var string
     */
    private $pathToArchive;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->pathToTar  = Util\Arr::getValue($conf, 'pathToTar');
        $this->showStdErr = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->path       = Util\Arr::getValue($conf, 'path');
        $this->removeDir  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'removeDir', ''), false);

        if (empty($this->path)) {
            throw new Exception('path option is mandatory');
        }
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        // set uncompressed default MIME type
        $target->setMimeType('application/x-tar');
        $status = Status::create();
        $tar    = $this->execute($target);

        // if tar doesn't handle the compression mark status uncompressed so the app can take care of compression
        if (!$this->executable->handlesCompression()) {
            $status->uncompressed($target->getPathnamePlain());
        }

        $result->debug($tar->getCmd());

        if (!$tar->wasSuccessful()) {
            throw new Exception('tar failed');
        }

        return $status;
    }

    /**
     * Setup the Executable to run the 'tar' command.
     *
     * @param  \phpbu\App\Backup\Target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            // check if tar supports requested compression
            if ($target->shouldBeCompressed()) {
                if (!Executable\Tar::isCompressorValid($target->getCompressor()->getCommand())) {
                    $this->pathToArchive = $target->getPathnamePlain();
                } else {
                    // compression could be handled by the tar command
                    $this->pathToArchive = $target->getPathname();
                    $this->compression   = $target->getCompressor()->getCommand();
                }
            } else {
                // no compression at all
                $this->pathToArchive = $target->getPathname();
            }
            $this->executable = new Executable\Tar($this->pathToTar);
            $this->executable->archiveDirectory($this->path)
                             ->useCompression($this->compression)
                             ->archiveTo($this->pathToArchive)
                             ->showStdErr($this->showStdErr);
        }
        return $this->executable;
    }
}
