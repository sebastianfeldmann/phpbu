<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Result as CliResult;
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
class Tar extends SimulatorExecutable implements Simulator
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
     * Path to backup
     *
     * @var string
     */
    private $path;

    /**
     * List of paths to exclude
     * --exclude
     *
     * @var array
     */
    private $excludes;

    /**
     * Special compression program
     * --use-compress-program
     *
     * @var string
     */
    private $compressProgram;

    /**
     * Tar should ignore failed reads
     * --ignore-failed-read
     *
     * @var boolean
     */
    private $ignoreFailedRead;

    /**
     * Remove the packed data
     *
     * @var boolean
     */
    private $removeSourceDir;

    /**
     * Compression to use.
     *
     * @var string
     */
    private $compression = '';

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
    public function setup(array $conf = [])
    {
        $this->pathToTar        = Util\Arr::getValue($conf, 'pathToTar', '');
        $this->path             = Util\Arr::getValue($conf, 'path', '');
        $this->excludes         = Util\Str::toList(Util\Arr::getValue($conf, 'exclude', ''));
        $this->compressProgram  = Util\Arr::getValue($conf, 'compressProgram', '');
        $this->ignoreFailedRead = Util\Str::toBoolean(Util\Arr::getValue($conf, 'ignoreFailedRead', ''), false);
        $this->removeSourceDir  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'removeSourceDir', ''), false);

        if (empty($this->path)) {
            throw new Exception('path option is mandatory');
        }
    }

    /**
     * Execute the backup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result) : Status
    {
        // make sure source path is a directory
        $this->validatePath();
        // set uncompressed default MIME type
        $target->setMimeType('application/x-tar');
        $tar = $this->execute($target);

        $result->debug($tar->getCmdPrintable());

        if ($this->tarFailed($tar)) {
            throw new Exception('tar failed: ' . $tar->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Check if tar failed.
     *
     * @param  \phpbu\App\Cli\Result
     * @return bool
     */
    public function tarFailed(CliResult $tar) : bool
    {
        $ignoreFail = $this->ignoreFailedRead && !$tar->isSuccessful();
        return !$tar->isSuccessful() && !$ignoreFail;
    }

    /**
     * Setup the Executable to run the 'tar' command.
     *
     * @param  \phpbu\App\Backup\Target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        // check if tar supports requested compression
        if ($target->shouldBeCompressed()) {
            if (!Executable\Tar::isCompressionValid($target->getCompression()->getCommand())) {
                $this->pathToArchive = $target->getPathnamePlain();
            } else {
                // compression can be handled by the tar command
                $this->pathToArchive = $target->getPathname();
                $this->compression   = $target->getCompression()->getCommand();
            }
        } else {
            // no compression at all
            $this->pathToArchive = $target->getPathname();
        }

        $executable = new Executable\Tar($this->pathToTar);
        $executable->archiveDirectory($this->path)
                   ->useCompression($this->compression)
                   ->useCompressProgram($this->compressProgram)
                   ->ignoreFailedRead($this->ignoreFailedRead)
                   ->removeSourceDirectory($this->removeSourceDir)
                   ->archiveTo($this->pathToArchive);
        // add paths to exclude
        foreach ($this->excludes as $path) {
            $executable->addExclude($path);
        }

        return $executable;
    }

    /**
     * Check the source to compress.
     *
     * @throws \phpbu\App\Exception
     */
    private function validatePath()
    {
        if (!is_dir($this->path)) {
            throw new Exception('path to compress has to be a directory');
        }
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        $status = Status::create();
        // if tar doesn't handle the compression mark status uncompressed
        // so the app can take care of compression
        if (!$this->executable->handlesCompression()) {
            $status->uncompressedFile($target->getPathnamePlain());
        }
        return $status;
    }
}
