<?php
namespace phpbu\Backup\Source;

use phpbu\App\Result;
use phpbu\Backup\Cli;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Util;

class Tar implements Source
{
    /**
     * Executor to run the tar shell command.
     *
     * @var \phpbu\Cli\Exec
     */
    private $exec;

    /**
     * Configuration
     *
     * @var array
     */
    private $conf;

    /**
     * Setup.
     *
     * @see    phpbu\Backup\Source
     * @param  array               $conf
     * @throws \RuntimeException
     */
    public function setup(array $conf = array())
    {
        $this->conf = $conf;
    }

    /**
     *
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @return \phpbu\App\Result
     */
    public function backup(Target $target, Result $result)
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

        $path = isset($this->conf['pathToTar']) ? $this->conf['pathToTar'] : null;
        $tar  = Util\Cli::detectCmdLocation('tar', $path);
        $cmd  = new Cli\Cmd($tar);
        $cmd->addOption(
            '-' . $compressOption . 'cf',
            array(
                $target->getPathname(true),
                $this->conf['path'],
            )
        );
        $this->exec->addCommand($cmd);

        $r = $this->exec->execute();

        $result->debug($r->getCmd());

        if (!$r->wasSuccessful()) {
            throw new Exception('tar failed');
        }

        return $result;
    }
}
