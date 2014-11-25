<?php
namespace phpbu\Backup\Cli;

use phpbu\Backup\Cli\Cmd;
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
class Exec
{
    /**
     * List of system commands to execute.
     *
     * @var array<\phpbu\Backup\Cli\Cmd>
     */
    private $commands = array();

    /**
     * Backup target
     *
     * @var \phpbu\Backup\Target
     */
    private $target;

    /**
     * Do we use the commands output for compression
     *
     * @var boolean
     */
    private $compressOutput = true;

    /**
     * Target settter
     *
     * @param \phpbu\Backup\Target $target
     */
    public function setTarget(Target $target)
    {
        $this->target = $target;
    }

    /**
     * OutputCompression setter
     *
     * @param boolean $bool
     */
    public function setOutputCompression($bool)
    {
        $this->compressOutput = $bool;
    }

    /**
     *
     * @throws \phpbu\App\Exception
     * @return \phpbu\Cli\Result
     */
    public function execute()
    {
        $cmd    = $this->getExec();
        $output = array();
        $code   = 0;
        $old    = error_reporting(0);
        exec($cmd, $output, $code);
        if ($this->compressOutput && $this->target->shouldBeCompressed()) {
            $compressorCode   = 0;
            $compressorOutput = array();
            exec($this->target->getCompressor()->getCommand() . ' -f ' . $this->target->getPathname(), $compressorOutput, $compressorCode);

            $code   += $compressorCode;
            $output = array_merge($output, $compressorOutput);
        }
        error_reporting($old);

        $result = new Result($cmd, $code, $output);
        if (!$result->wasSuccessful()) {
            // remove possible targets
            if (file_exists($this->target->getPathname())) {
                unlink($this->target->getPathname());
            }
            if ($this->target->shouldBeCompressed() && file_exists($this->target->getPathname(true))) {
                unlink($this->target->getPathname(true));
            }
        }
        return $result;
    }

    /**
     * Generates the system command.
     *
     * @throws \phpbu\App\Exception
     * @return string
     */
    public function getExec()
    {
        $amount = count($this->commands);
        if ($amount < 1) {
            throw new Exception('no command to execute');
        }
        $cmd = $amount > 1 ? '(' . implode(' && ', $this->commands) . ')' : $this->commands[0];

        if ($this->compressOutput) {
            $cmd .= ' > ' . $this->target->getPathname();
        }
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
