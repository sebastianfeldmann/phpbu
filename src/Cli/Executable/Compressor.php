<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

/**
 * Compressor class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Compressor extends Abstraction implements Executable
{
    /**
     * File to compress.
     *
     * @var string
     */
    protected $fileToCompress;

    /**
     * Force overwrite.
     *
     * @var boolean
     */
    protected $force = false;

    /**
     * Constructor.
     *
     * @param string $cmd
     * @param string $path
     */
    public function __construct($cmd, $path = null)
    {
        $this->cmd = $cmd;
        parent::__construct($path);
    }

    /**
     * Set the file to compress.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Compressor
     * @throws \phpbu\App\Exception
     */
    public function compressFile($path)
    {
        if (!file_exists($path)) {
            throw new Exception('file does not exist: ' . $path);
        }
        $this->fileToCompress = $path;
        return $this;
    }

    /**
     * Use '-f' force mode.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Compressor
     */
    public function force($bool)
    {
        $this->force = $bool;
        return $this;
    }

    /**
     * Process generator
     *
     * @throws \phpbu\App\Exception
     */
    protected function createProcess()
    {
        // make sure there is a file to compress
        if (empty($this->fileToCompress)) {
            throw new Exception('file to compress not set');
        }
        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        // don't add '-f' option for 'zip' executable issue #34
        if ($this->cmd !== 'zip') {
            $cmd->addOptionIfNotEmpty('-f', $this->force, false);
        }
        $cmd->addArgument($this->fileToCompress);

        return $process;
    }
}
