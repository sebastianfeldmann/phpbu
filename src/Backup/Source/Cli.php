<?php
namespace phpbu\Backup\Source;

use phpbu\Backup\Cli\Exec;
use phpbu\Backup\Cli\Result;
use phpbu\Backup\Target;

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
     * Executes the cli commands and handles compression
     *
     * @param  \phpbu\Backup\Cli\Exec $exec
     * @param  \phpbu\Backup\Target   $target
     * @param  bool                   $compressOutput
     * @return \phpbu\Backup\Cli\Result
     * @throws \phpbu\App\Exception
     */
    protected function execute(Exec $exec, Target $target, $compressOutput = true)
    {
        /** @var \phpbu\Backup\Cli\Result $res */
        $res    = $exec->execute($compressOutput ? $target->getPathname() : null);
        $code   = $res->getCode();
        $cmd    = $res->getCmd();
        $output = $res->getOutput();

        if ($code == 0) {
            // run the compressor command
            if ($compressOutput && $target->shouldBeCompressed()) {
                $compressorCode   = 0;
                $compressorOutput = array();
                $compressorCmd    = $target->getCompressor()->getCommand();
                $old              = error_reporting(0);
                exec(
                    $compressorCmd
                    . ' -f '
                    . $target->getPathname(),
                    $compressorOutput,
                    $compressorCode
                );
                error_reporting($old);

                if ($compressorCode !== 0) {
                    // remove compressed file with errors
                    if ($target->fileExists()) {
                        $target->unlink();
                    }
                }

                $cmd   .= PHP_EOL . $compressorCmd;
                $code  += $compressorCode;
                $output = array_merge($output, $compressorOutput);
            }
        } else {
            // remove file with errors
            if ($target->fileExists(false)) {
                $target->unlink(false);
            }
        }

        return new Result($cmd, $code, $output);
    }
}
