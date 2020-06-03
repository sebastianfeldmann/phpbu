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
     * Path to the incremental metadata file
     *
     * @var string
     */
    private $incrementalFile;

    /**
     * Force level 0 backup on
     *
     * - DATE@VLAUE
     * - %h@3   => every 3am backup
     * - %d@1   => first each month
     * - %D@Mon => every Monday
     *
     * @var string
     */
    private $forceLevelZeroOn;

    /**
     * Should a level zero backup be forced
     *
     * @var bool
     */
    private $forceLevelZero = false;

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
        $this->incrementalFile  = Util\Arr::getValue($conf, 'incrementalFile', '');
        $this->forceLevelZeroOn = Util\Arr::getValue($conf, 'forceLevelZeroOn', '');
        $this->compressProgram  = Util\Arr::getValue($conf, 'compressProgram', '');
        $this->throttle         = Util\Arr::getValue($conf, 'throttle', '');
        $this->forceLocal       = Util\Str::toBoolean(Util\Arr::getValue($conf, 'forceLocal', ''), false);
        $this->ignoreFailedRead = Util\Str::toBoolean(Util\Arr::getValue($conf, 'ignoreFailedRead', ''), false);
        $this->removeSourceDir  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'removeSourceDir', ''), false);
        $this->dereference      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dereference', ''), false);
        $this->setupIncrementalSettings();
    }

    /**
     * Setup incremental backup settings
     *
     * @throws \phpbu\App\Exception
     */
    private function setupIncrementalSettings()
    {
        // no incremental backup just bail
        if (empty($this->incrementalFile)) {
            return;
        }
        $this->incrementalFile = $this->toAbsolutePath($this->incrementalFile);

        // no zero level forcing, bail again
        if (empty($this->forceLevelZeroOn)) {
            return;
        }
        // extract the date placeholder %D and the values a|b|c from the configuration
        // %DATE@VALUE[|VALUE]
        // - %D@Mon
        // - %d@1|11|22
        // - %D@Sun|Thu
        $dateAndValues = explode('@', $this->forceLevelZeroOn);
        $date          = $dateAndValues[0] ?? '';
        $values        = $dateAndValues[1] ?? '';

        if (empty($date) || empty($values)) {
            throw new Exception('invalid \'forceLevelZeroOn\' configuration - \'%DATE@VALUE[|VALUE]\'');
        }
        // check if the given date format is happening right now
        $this->forceLevelZero = $this->isLevelZeroTime($date, explode('|', $values));
    }

    /**
     * Checks if the configured zero level force applies to the current time
     *
     * @param  string $date
     * @param  array  $values
     * @return bool
     */
    private function isLevelZeroTime(string $date, array $values): bool
    {
        $currentDateValue = Util\Path::replaceDatePlaceholders($date, $this->time);
        foreach ($values as $configuredValue) {
            if ($currentDateValue === $configuredValue) {
                return true;
            }
        }
        return false;
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

        $this->handleIncrementalBackup($executable);

        // add paths to exclude
        foreach ($this->excludes as $path) {
            $executable->addExclude($path);
        }

        return $executable;
    }

    /**
     * Setup the incremental backup options
     *
     * @param  \phpbu\App\Cli\Executable\Tar $executable
     * @throws \phpbu\App\Exception
     * @return void
     */
    private function handleIncrementalBackup(Executable\Tar $executable): void
    {
        if (empty($this->incrementalFile)) {
            return;
        }
        $executable->incrementalMetadata($this->incrementalFile);
        $executable->forceLevelZero($this->forceLevelZero);
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
