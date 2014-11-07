<?php
namespace phpbu\Backup\Runner;

use phpbu\Backup\Runner;
use phpbu\Backup\Runner\Cli\Cmd;
use phpbu\Backup\Runner\Result;
use phpbu\Backup\Target;

/**
 * Cli Runner
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Cli implements Runner
{
    /**
     * List of system commands to execute.
     *
     * @var array<phpbu\Backup\Runner\Cli\Cmd>
     */
    private $commands = array();

    /**
     * Backup target
     *
     * @var phpbu\Backup\Target
     */
    private $target;

    /**
     * Target settter
     *
     * @param phpbu\Backup\Target $target
     */
    public function setTarget(Target $target)
    {
        $this->target = $target;
    }

    /**
     *
     * @throws phpbu\App\Exception
     * @return phpbu\Backup\Runner\Result
     */
    public function run()
    {
        $cmd    = $this->getExec();
        $output = array();
        $code   = null;
        $old    = error_reporting(0);
        exec($cmd, $output, $code);
        error_reporting($old);

        return new Result($cmd, $code, $output);
    }

    /**
     * Generates the system command.
     *
     * @throws phpbu\App\Exception
     * @return string
     */
    public function getExec()
    {
        $amount = count($this->commands);
        if ($amount < 1) {
            throw new Exception('no command to execute');
        }
        $cmd = $amount > 1 ? '(' . implode(' && ', $this->commands) . ')' : $this->commands[0];

        if ($this->target->shouldBeCompressed()) {
            $cmd    .= ' | ' . $this->target->getCompressor()->getCommand();
        }
        $cmd .= ' > ' . (string) $this->target;

        return $cmd;
    }

    /**
     * Adds a system command to the command list.
     *
     * @param phpbu\Backup\Runner\Cli\Cmd $cmd
     */
    public function addCommand(Cmd $cmd)
    {
        $this->commands[] = $cmd;
    }
}
