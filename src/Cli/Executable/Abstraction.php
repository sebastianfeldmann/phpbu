<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Process;
use phpbu\App\Util\Cli;

/**
 * Execute Binary
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.2
 */
abstract class Abstraction
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $cmd;

    /**
     * Absolute path to command.
     *
     * @var string
     */
    protected $binary;

    /**
     * Command to execute
     *
     * @var \phpbu\App\Cli\Process
     */
    protected $process;

    /**
     * Display stdErr
     *
     * @var boolean
     */
    protected $showStdErr = false;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null) {
        $this->binary = Cli::detectCmdLocation($this->cmd, $path, Cli::getCommandLocations($this->cmd));
    }

    /**
     * Process setter, mostly for test purposes.
     *
     * @param \phpbu\App\Cli\Process $process
     */
    public function setProcess(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Returns the Process for this command.
     *
     * @return \phpbu\App\Cli\Process
     */
    public function getProcess()
    {
        if ($this->process == null) {
            $this->process = $this->createProcess();
        }
        return $this->process;
    }

    /**
     * Subclass Process generator.
     *
     * @return \phpbu\App\Cli\Process
     */
    protected abstract function createProcess();

    /**
     * Show the stdError output.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable
     */
    public function showStdErr($bool)
    {
        $this->showStdErr = $bool;
        return $this;
    }

    /**
     * Executes the cli commands.
     *
     * @return \phpbu\App\Cli\Result
     * @throws \phpbu\App\Exception
     */
    public function run()
    {
        $process = $this->getProcess();
        $res     = $process->run();

        if (0 !== $res->getCode() && $process->isOutputRedirected()) {
            // remove file with errors
            $this->unlinkErrorFile($process->getRedirectPath());
        }

        return $res;
    }

    /**
     * Return the command line to execute.
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getCommandLine()
    {
        return $this->getProcess()->getCommandLine();
    }

    /**
     * Remove file if it exists.
     *
     * @param string $file
     */
    public function unlinkErrorFile($file)
    {
        if (file_exists($file) && !is_dir($file)) {
            unlink($file);
        }
    }
}
