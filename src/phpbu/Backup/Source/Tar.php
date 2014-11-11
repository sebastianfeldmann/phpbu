<?php
namespace phpbu\Backup\Source;

use phpbu\App\Result;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Cli;
use phpbu\Util;

class Tar implements Source
{
    /**
     * Executor to run the tar shell command.
     *
     * @var phpbu\Cli\Exec
     */
    private $exec;

    /**
     * Setup.
     *
     * @see    phpbu\Backup\Source
     * @param  phpbu\Backup\Target $target
     * @param  array               $conf
     * @throws RuntimeException
     */
    public function setup(Target $target, array $conf = array())
    {
        $this->exec = new Cli\Exec();
        $this->exec->setTarget($target);
        $this->exec->setOutputCompression(false);

        $compressOption     = '';
        $allowedCompressors = array(
            'bzip2' => 'j',
            'gzip'  => 'z',
        );

        // check if 'tar' can handle the requested compression
        if ($target->shouldBeCompressed()) {
            $compressor = $target->getCompressor();
            $name       = $compressor->getCommand(false);
            if (isset($allowedCompressors[$name])) {
                $compressOption = $allowedCompressors[$name];
            } else {
                // the requested compression is not available for the 'tar' command
                $target->disableCompression();
            }
        }

        $path = isset($conf['pathToTar']) ? $conf['pathToTar'] : null;
        $tar  = Util\Cli::detectCmdLocation('tar', $path);
        $cmd  = new Cli\Cmd($tar);
        $cmd->addOption(
            '-' . $compressOption . 'cvf',
            array(
                (string) $target,
                $conf['dir'],
            )
        );
        $this->exec->addCommand($cmd);
    }

    /**
     *
     * @param  phpbu\App\Result $result
     * @return phpbu\App\Result
     */
    public function backup(Result $result)
    {
        $r = $this->exec->execute();

        echo $r->getCmd() . PHP_EOL;

        return $result;
    }
}
