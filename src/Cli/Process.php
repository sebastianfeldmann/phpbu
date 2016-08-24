<?php
namespace phpbu\App\Cli;

use phpbu\App\Exception;
use phpbu\App\Util\Cli;

/**
 * Cli Process Runner
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Process
{
    /**
     * List of system commands to execute.
     *
     * @var \phpbu\App\Cli\Cmd[]
     */
    private $commands = [];

    /**
     * Redirect the output
     *
     * @var string
     */
    private $redirectOutput;

    /**
     * Output pipeline
     *
     * @var \phpbu\App\Cli\Cmd[]
     */
    private $pipeline = [];

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
     * Pipe the command into given command.
     *
     * @param  \phpbu\App\Cli\Cmd $cmd
     * @throws \phpbu\App\Exception
     */
    public function pipeOutputTo(Cmd $cmd)
    {
        if (!Cli::canPipe()) {
            throw new Exception('Can\'t pipe output');
        }
        $this->pipeline[] = $cmd;
    }

    /**
     * Is there a command pipeline.
     *
     * @return bool
     */
    public function isPiped()
    {
        return !empty($this->pipeline);
    }

    /**
     * Return command pipeline.
     *
     * @return string
     */
    public function getPipeline()
    {
        return $this->isPiped() ? ' | ' . implode(' | ', $this->pipeline) : '';
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
        $cmd = ($amount > 1 ? '(' . implode(' && ', $this->commands) . ')' : $this->commands[0])
             . $this->getPipeline()
             . (!empty($this->redirectOutput) ? ' > ' . $this->redirectOutput : '');

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
        $cmd            = $this->getCommandLine();
        $old            = error_reporting(0);
        $descriptorSpec = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            throw new Exception('can\'t execute \'proc_open\'');
        }

        $stdOut = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stdErr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $code = proc_close($process);
        error_reporting($old);

        return new Result($cmd, $code, $stdOut, $stdErr);
    }
}
