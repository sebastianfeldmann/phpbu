<?php
namespace phpbu\Backup\Cli;

use phpbu\App\Exception;

/**
 * Cli Runner
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Exec
{
    /**
     * List of system commands to execute.
     *
     * @var array<\phpbu\Backup\Cli\Cmd>
     */
    private $commands = array();

    /**
     * Executes the commands.
     *
     * @param  string $redirect
     * @return \phpbu\Backup\Cli\Result
     * @throws \phpbu\App\Exception
     */
    public function execute($redirect = null)
    {
        $cmd    = $this->getExec() . ( $redirect ? ' > ' . $redirect : '' );
        $output = array();
        $code   = 0;
        $old    = error_reporting(0);
        exec($cmd, $output, $code);
        error_reporting($old);

        return new Result($cmd, $code, $output);
    }

    /**
     * Generates the system command.
     * 
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getExec()
    {
        $amount = count($this->commands);
        if ($amount < 1) {
            throw new Exception('no command to execute');
        }
        $cmd = $amount > 1 ? '(' . implode(' && ', $this->commands) . ')' : $this->commands[0];

        return $cmd;
    }

    /**
     * Adds a system command to the command list.
     *
     * @param \phpbu\Backup\Cli\Cmd $cmd
     */
    public function addCommand(Cmd $cmd)
    {
        $this->commands[] = $cmd;
    }
}
