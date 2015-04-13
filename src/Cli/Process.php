<?php
namespace phpbu\App\Cli;

use phpbu\App\Exception;

/**
 * Cli Process Runner
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Process
{
    /**
     * List of system commands to execute.
     *
     * @var array<\phpbu\Backup\Cli\Cmd>
     */
    private $commands = array();

    /**
     * Redirect the output
     *
     * @var string
     */
    private $redirectOutput;

    /**
     * Redirect the stdOut.
     *
     * @param string $path
     */
    public function redirectOutputTo($path)
    {
        $this->redirectOutput = $path;
    }

    /**
     * Should the output be redirected.
     *
     * @return boolean
     */
    public function isOutputRedirected()
    {
        return !empty($this->redirectOutput);
    }

    /**
     * Redirect getter.
     *
     * @return string
     */
    public function getRedirectPath()
    {
        return $this->redirectOutput;
    }

    /**
     * Adds a cli command to the command list.
     *
     * @param \phpbu\App\Cli\Cmd $cmd
     */
    public function addCommand(Cmd $cmd)
    {
        $this->commands[] = $cmd;
    }

    /**
     * Generates the system command.
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getCommandLine()
    {
        $amount = count($this->commands);
        if ($amount < 1) {
            throw new Exception('no command to execute');
        }
        $cmd = ($amount > 1                   ? '(' . implode(' && ', $this->commands) . ')' : $this->commands[0])
             . (!empty($this->redirectOutput) ? ' > ' . $this->redirectOutput                : '');

        return $cmd;
    }

    /**
     * Executes the commands.
     *
     * @return \phpbu\App\Cli\Result
     * @throws \phpbu\App\Exception
     */
    public function run()
    {
        $cmd    = $this->getCommandLine();
        $output = array();
        $code   = 0;
        $old    = error_reporting(0);
        exec($cmd, $output, $code);
        error_reporting($old);

        return new Result($cmd, $code, $output);
    }
}
