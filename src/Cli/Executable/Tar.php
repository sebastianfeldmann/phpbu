<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Tar Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Tar extends Abstraction implements Executable
{
    /**
     * Path to compress
     *
     * @var string
     */
    private $path;

    /**
     * Compression to use
     *
     * @var string
     */
    private $compression;

    /**
     * Compress program to use.
     * --use-compress-program
     *
     * @var string
     */
    private $compressProgram;

    /**
     * Path to dump file
     *
     * @var string
     */
    private $tarPathname;

    /**
     * List of excluded path.
     * --exclude='foo'
     *
     * @var array
     */
    private $excludes = [];

    /**
     * Force local file resolution
     * --force-local
     *
     * @var bool
     */
    private $local = false;

    /**
     * Ignore failed reads
     * --ignore-failed-read
     *
     * @var bool
     */
    private $ignoreFailedRead;

    /**
     * Should the source directory be removed.
     *
     * @var boolean
     */
    private $removeSourceDir = false;

    /**
     * Limit data throughput
     * | pv -L ${limit}
     *
     * @var string
     */
    private $pvLimit = '';

    /**
     * List of available compressors
     *
     * @var array
     */
    private static $availableCompressions = [
        'bzip2' => 'j',
        'gzip'  => 'z',
        'xz'    => 'J'
    ];

    /**
     * Instead of archiving symbolic links, archive the files they link to
     *
     * @var bool
     */
    private $dereference = false;

    /**
     * File to store the incremental metadata in 'archive.snar'
     *
     * @var string
     */
    private $metadataFile = '';

    /**
     * Defines the incremental backup level
     *
     * This is used during incremental backup only.
     *
     *  0 => backup all
     *  1 => backup incremental
     *
     * @var int
     */
    private $level = 1;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('tar', $path);
    }

    /**
     * Return 'tar' compressor option e.g. 'j' for bzip2.
     *
     * @param  string $compressor
     * @return string
     */
    protected function getCompressionOption(string $compressor) : string
    {
        return $this->isCompressionValid($compressor) ? self::$availableCompressions[$compressor] : '';
    }

    /**
     * Compress tar.
     *
     * @param  string $compression
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function useCompression(string $compression) : Tar
    {
        if ($this->isCompressionValid($compression)) {
            $this->compression = $this->getCompressionOption($compression);
        }
        return $this;
    }

    /**
     * Set compress program.
     *
     * @param  string $program
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function useCompressProgram(string $program) : Tar
    {
        $this->compressProgram = $program;
        return $this;
    }

    /**
     * Add an path to exclude.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function addExclude(string $path) : Tar
    {
        $this->excludes[] = $path;
        return $this;
    }

    /**
     * Force local file resolution.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function forceLocal(bool $bool) : Tar
    {
        $this->local = $bool;
        return $this;
    }

    /**
     * Ignore failed reads setter.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function ignoreFailedRead(bool $bool) : Tar
    {
        $this->ignoreFailedRead = $bool;
        return $this;
    }

    /**
     * Limit the data throughput.
     *
     * @param string $limit
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function throttle(string $limit) : Tar
    {
        $this->pvLimit = $limit;
        return $this;
    }

    /**
     * Does the tar handle the compression.
     *
     * @return bool
     */
    public function handlesCompression() : bool
    {
        return !empty($this->compression);
    }

    /**
     * Set folder to compress.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Tar
     * @throws \phpbu\App\Exception
     */
    public function archiveDirectory(string $path) : Tar
    {
        $this->validateDirectory($path);
        $this->path = $path;
        return $this;
    }

    /**
     * Set target filename.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function archiveTo(string $path) : Tar
    {
        $this->tarPathname = $path;
        return $this;
    }

    /**
     * Delete the source directory.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function removeSourceDirectory(bool $bool) : Tar
    {
        $this->removeSourceDir = $bool;
        return $this;
    }

    /**
     * Instead of archiving symbolic links, archive the files they link
     *
     * @param bool $bool
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function dereference(bool $bool) : Tar
    {
        $this->dereference = $bool;
        return $this;
    }

    /**
     * Set incremental backup metadata file
     *
     * @param  string $pathToMetadataFile
     * @return $this
     */
    public function incrementalMetadata(string $pathToMetadataFile): Tar
    {
        $this->metadataFile = $pathToMetadataFile;
        return $this;
    }

    /**
     * Force a level 0 backup even if backup is done incrementally
     *
     * @param bool $levelZero
     */
    public function forceLevelZero(bool $levelZero)
    {
        $this->level = $levelZero ? 0 : 1;
    }

    /**
     * Tar CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $this->validateSetup();

        $process = new CommandLine();
        $tar     = new Cmd($this->binary);
        $create  = $this->isThrottled() ? 'c' : 'cf';
        $process->addCommand($tar);

        $this->setExcludeOptions($tar);
        $this->handleWarnings($tar);
        $this->handleIncremental($tar);

        $tar->addOptionIfNotEmpty('-h', $this->dereference, false);
        $tar->addOptionIfNotEmpty('--force-local', $this->local, false);
        $tar->addOptionIfNotEmpty('--use-compress-program', $this->compressProgram);
        $tar->addOption('-' . (empty($this->compressProgram) ? $this->compression : '') . $create);

        if ($this->isThrottled()) {
            $pv = new Cmd('pv');
            $pv->addOption('-qL', $this->pvLimit, ' ');
            $process->pipeOutputTo($pv);
            $process->redirectOutputTo($this->tarPathname);
        } else {
            $tar->addArgument($this->tarPathname);
        }

        $tar->addOption('-C', dirname($this->path), ' ');
        $tar->addArgument(basename($this->path));

        // delete the source data if requested
        $this->addRemoveCommand($process);

        return $process;
    }

    /**
     * Adds necessary exclude options to tat command.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $tar
     */
    protected function setExcludeOptions(Cmd $tar)
    {
        foreach ($this->excludes as $path) {
            $tar->addOption('--exclude', $path);
        }
    }

    /**
     * Configure warning handling.
     * With the 'ignoreFailedRead' option set, exit code '1' is also accepted since it only indicates a warning.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $tar
     */
    protected function handleWarnings(Cmd $tar)
    {
        if ($this->ignoreFailedRead) {
            $tar->addOption('--ignore-failed-read');
            $this->acceptableExitCodes = [0, 1];
        }
    }

    /**
     * Will set the incremental backup options
     *
     * - --listed-incremental=PATH_TO_METADATA_FILE
     * - --level=0|1
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $tar
     */
    private function handleIncremental(Cmd $tar)
    {
        // if no incremental metadata file is set we can skip this part
        if (empty($this->metadataFile)) {
            return;
        }

        $tar->addOption('--listed-incremental', $this->metadataFile);

        // only set the level if we want to force a level 0 backup since 1 is the default value
        if ($this->level !== 1) {
            $tar->addOption('--level', $this->level);
        }
    }

    /**
     * Add a remove command if requested.
     *
     * @param \SebastianFeldmann\Cli\CommandLine $process
     */
    protected function addRemoveCommand(CommandLine $process)
    {
        if ($this->removeSourceDir) {
            $process->addCommand($this->getRmCommand());
        }
    }

    /**
     * Return 'rm' command.
     *
     * @return \SebastianFeldmann\Cli\Command\Executable
     */
    protected function getRmCommand() : Cmd
    {
        $rm = new Cmd('rm');
        $rm->addOption('-rf', $this->path, ' ');
        return $rm;
    }

    /**
     * Check directory to compress.
     *
     * @param  string $path
     * @throws \phpbu\App\Exception
     */
    private function validateDirectory(string $path)
    {
        if ($path === '.') {
            throw new Exception('unable to tar current working directory');
        }
    }

    /**
     * Check if source and target values are set.
     *
     * @throws \phpbu\App\Exception
     */
    private function validateSetup()
    {
        if (empty($this->path)) {
            throw new Exception('no directory to compress');
        }
        if (empty($this->tarPathname)) {
            throw new Exception('no target filename set');
        }
    }

    /**
     * Return true if a given compression is valid false otherwise.
     *
     * @param  string $compression
     * @return bool
     */
    public static function isCompressionValid(string  $compression) : bool
    {
        return isset(self::$availableCompressions[$compression]);
    }

    /**
     * Should output be throttled through pv.
     *
     * @return bool
     */
    public function isThrottled() : bool
    {
        return !empty($this->pvLimit);
    }
}
