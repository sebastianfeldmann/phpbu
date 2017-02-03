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
     * Path to dump file
     *
     * @var string
     */
    private $tarPathname;

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
     * List of available compressors
     *
     * @var array
     */
    private static $availableCompressions = [
        'bzip2' => 'j',
        'gzip'  => 'z',
    ];

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
     * Tar CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     */
    protected function createCommandLine() : CommandLine
    {
        $this->validateSetup();

        $process = new CommandLine();
        $tar     = new Cmd($this->binary);

        $tar->addOptionIfNotEmpty('--ignore-failed-read', $this->ignoreFailedRead, false);
        $tar->addOption('-' . $this->compression . 'cf');
        $tar->addArgument($this->tarPathname);
        $tar->addOption('-C', dirname($this->path), ' ');
        $tar->addArgument(basename($this->path));

        $process->addCommand($tar);

        // delete the source data if requested
        if ($this->removeSourceDir) {
            $process->addCommand($this->getRmCommand());
        }

        return $process;
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
}
