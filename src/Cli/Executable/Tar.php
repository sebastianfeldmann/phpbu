<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

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
    private static $availableCompressors = array(
        'bzip2' => 'j',
        'gzip'  => 'z',
    );

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->cmd = 'tar';
        parent::__construct($path);
    }

    /**
     * Return 'tar' compressor option e.g. 'j' for bzip2.
     *
     * @param  $compressor
     * @return string
     */
    protected function getCompressorOption($compressor)
    {
        return $this->isCompressorValid($compressor) ? self::$availableCompressors[$compressor] : null;
    }

    /**
     * Compress tar.
     *
     * @param  string $compressor
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function useCompression($compressor)
    {
        if ($this->isCompressorValid($compressor)) {
            $this->compression = $this->getCompressorOption($compressor);
        }
        return $this;
    }

    /**
     * Doe the tar handle the compression.
     *
     * @return boolean
     */
    public function handlesCompression()
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
    public function archiveDirectory($path)
    {
        if (!is_dir($path)) {
            throw new Exception('patch to archive has to be a directory');
        }
        $this->path = $path;
        return $this;
    }

    /**
     * Set target filename.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Tar
     */
    public function archiveTo($path)
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
    public function removeSourceDirectory($bool)
    {
        $this->removeSourceDir = $bool;
        return $this;
    }

    /**
     * Process generator
     */
    protected function createProcess()
    {
        // check source and target settings
        if (empty($this->path)) {
            throw new Exception('no directory to compress');
        }
        if (empty($this->tarPathname)) {
            throw new Exception('no target filename set');
        }

        $process = new Process();
        $tar     = new Cmd($this->binary);

        $tar->addOption('-' . $this->compression . 'cf');
        $tar->addArgument($this->tarPathname);
        $tar->addOption('-C', $this->path, ' ');
        $tar->addArgument('.');

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
     * @return \phpbu\App\Cli\Cmd
     */
    protected function getRmCommand()
    {
        $rm = new Cmd('rm');
        $rm->addOption('-rf', $this->path, ' ');
        return $rm;
    }

    /**
     * Return true if a given compressor is valid false otherwise.
     *
     * @param  string $compressor
     * @return boolean
     */
    public static function isCompressorValid($compressor)
    {
        return isset(self::$availableCompressors[$compressor]);
    }
}
