<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Configuration;
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
class Tar extends SimulatorExecutable implements Simulator, Restorable
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
     * Force local file resolution
     *
     * --force-local
     *
     * @var bool
     */
    private $forceLocal;

    /**
     * Tar should ignore failed reads
     * --ignore-failed-read
     *
     * @var bool
     */
    private $ignoreFailedRead;

    /**
     * Remove the packed data
     *
     * @var bool
     */
    private $removeSourceDir;

    /**
     * Compression to use.
     *
     * @var string
     */
    private $compression = '';

    /**
     * Throttle cpu usage.
     *
     * @var string
     */
    private $throttle = '';

    /**
     * Path where to store the archive.
     *
     * @var string
     */
    private $pathToArchive;

    /**
     * Instead of archiving symbolic links, archive the files they link to
     *
     * @var bool
     */
    private $dereference;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupPath($conf);
        $this->pathToTar        = Util\Arr::getValue($conf, 'pathToTar', '');
        $this->excludes         = Util\Str::toList(Util\Arr::getValue($conf, 'exclude', ''));
        $this->compressProgram  = Util\Arr::getValue($conf, 'compressProgram', '');
        $this->throttle         = Util\Arr::getValue($conf, 'throttle', '');
        $this->forceLocal       = Util\Str::toBoolean(Util\Arr::getValue($conf, 'forceLocal', ''), false);
        $this->ignoreFailedRead = Util\Str::toBoolean(Util\Arr::getValue($conf, 'ignoreFailedRead', ''), false);
        $this->removeSourceDir  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'removeSourceDir', ''), false);
        $this->dereference      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dereference', ''), false);
    }

    /**
     * Setup the path to the directory that should be compressed.
     *
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    protected function setupPath(array $conf)
    {
        $path = Util\Arr::getValue($conf, 'path', '');
        if (empty($path)) {
            throw new Exception('path option is mandatory');
        }
        $this->path = Util\Path::toAbsolutePath($path, Configuration::getWorkingDirectory());
        if (!file_exists($this->path)) {
            throw new Exception('could not find directory to compress');
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

        if (!$tar->isSuccessful()) {
            throw new Exception('tar failed: ' . $tar->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Restore the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function restore(Target $target, Plan $plan): Status
    {
        $plan->addRestoreCommand('tar -xvf ' . $target->getFilename(true));
        return Status::create()->uncompressedFile($target->getPathname());
    }

    /**
     * Setup the Executable to run the 'tar' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        $this->pathToArchive = $target->getPathnamePlain();

        // check if archive should be compressed and tar supports requested compression
        if ($target->shouldBeCompressed()
            && Executable\Tar::isCompressionValid($target->getCompression()->getCommand())) {
            $this->pathToArchive = $target->getPathname();
            $this->compression   = $target->getCompression()->getCommand();
        }

        $executable = new Executable\Tar($this->pathToTar);
        $executable->archiveDirectory($this->path)
                   ->useCompression($this->compression)
                   ->useCompressProgram($this->compressProgram)
                   ->forceLocal($this->forceLocal)
                   ->ignoreFailedRead($this->ignoreFailedRead)
                   ->removeSourceDirectory($this->removeSourceDir)
                   ->throttle($this->throttle)
                   ->archiveTo($this->pathToArchive)
                   ->dereference($this->dereference);
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
     * @param  \phpbu\App\Backup\Target $target
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
