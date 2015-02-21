<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Cli\Exec;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Util;

class Tar extends Cli implements Source
{
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
     * Executes the backup
     *
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result $result
     * @return Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        $exec               = new Exec();
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
        $cmd  = new Cmd($tar);

        // no std error unless it is activated
        if (empty($this->conf['showStdErr']) || !Util\String::toBoolean($this->conf['showStdErr'], false)) {
            $cmd->silence();
        }

        $cmd->addOption(
            '-' . $compressOption . 'cf',
            array(
                $target->getPathname(true),
                $this->conf['path'],
            )
        );
        $exec->addCommand($cmd);

        $r = $this->execute($exec, $target, false);

        $result->debug($r->getCmd());

        if (!$r->wasSuccessful()) {
            throw new Exception('tar failed');
        }

        return $result;
    }
}
