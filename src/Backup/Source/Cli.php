<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Cli\Result;
use phpbu\App\Backup\Target;

/**
 * Cli Runner
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.3
 */
abstract class Cli
{
    /**
     * Path to mysqldump command
     *
     * @var string
     */
    protected $binary;

    /**
     * Executes the cli commands and handles compression
     *
     * @param  \phpbu\App\Backup\Cli\Exec $exec
     * @param  \phpbu\App\Backup\Target   $target
     * @param  bool                       $compressOutput
     * @return \phpbu\App\Backup\Cli\Result
     * @throws \phpbu\App\Exception
     */
    protected function execute(Exec $exec, Target $target, $compressOutput = true)
    {
        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res    = $exec->execute($compressOutput ? $target->getPathname() : null);
        $code   = $res->getCode();
        $cmd    = $res->getCmd();
        $output = $res->getOutput();

        if ($code == 0) {
            // run the compressor command
            if ($compressOutput && $target->shouldBeCompressed()) {
                // compress the generated output with configured compressor
                $res = $this->compressOutput($target);

                if ($res->getCode() !== 0) {
                    // remove compressed file with errors
                    if ($target->fileExists()) {
                        $target->unlink();
                    }
                }

                $cmd   .= PHP_EOL . $res->getCmd();
                $code  += $res->getCode();
                $output = array_merge($output, $res->getOutput());
            }
        } else {
            // remove file with errors
            if ($target->fileExists(false)) {
                $target->unlink(false);
            }
        }

        return new Result($cmd, $code, $output);
    }

    /**
     * Compress the generated output.
     *
     * @param  \phpbu\App\Backup\Target Target $target
     * @return \phpbu\App\Backup\Cli\Result
     */
    protected function compressOutput(Target $target)
    {
        $exec = $target->getCompressor()
                       ->getExec($target->getPathname(false), array('-f'));

        $old = error_reporting(0);
        $res = $exec->execute();
        error_reporting($old);

        return $res;
    }

    /**
     * Binary setter, mostly for test purposes.
     *
     * @param string $pathToMysqldump
     */
    public function setBinary($pathToMysqldump)
    {
        $this->binary = $pathToMysqldump;
    }

    /**
     * Adds an option to a command if it is not empty.
     *
     * @param \phpbu\App\Backup\Cli\Cmd $cmd
     * @param string                    $option
     * @param mixed                     $check
     * @param bool                      $asValue
     */
    protected function addOptionIfNotEmpty(Cmd $cmd, $option, $check, $asValue = true)
    {
        if (!empty($check)) {
            if ($asValue) {
                $cmd->addOption($option, $check);
            } else {
                $cmd->addOption($option);
            }
        }
    }
}
