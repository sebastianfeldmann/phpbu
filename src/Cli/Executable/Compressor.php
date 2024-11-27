<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

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
class Compressor extends Abstraction
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
    public function __construct(string $cmd, string $path = '')
    {
        $this->setup($cmd, $path);
    }

    /**
     * Set the file to compress.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Compressor
     */
    public function compressFile($path)
    {
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
     * Compressor CommandLine generator
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        // make sure there is a file to compress
        if (empty($this->fileToCompress)) {
            throw new Exception('file to compress not set');
        }
        $process = new CommandLine();
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
