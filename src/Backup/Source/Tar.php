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
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

    /**
     * Path to backup
     *
     * @var string
     */
    private $path;

    /**
     * List of available compressors
     *
     * @var array
     */
    private $compressors = array(
        'bzip2' => 'j',
        'gzip' => 'z',
    );

    /**
     * Setup.
     *
     * @see    phpbu\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupTar($conf);

        $this->showStdErr = Util\String::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->path = Util\Arr::getValue($conf, 'path');

        if (empty($this->path)) {
            throw new Exception('path option is mandatory');
        }
    }

    /**
     * Search for tar command.
     *
     * @param array $conf
     */
    protected function setupTar(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = Util\Cli::detectCmdLocation('tar', Util\Arr::getValue($conf, 'pathToTar'));
        }
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\Backup\Source
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result $result
     * @return Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        // set uncompressed default MIME type
        $target->setMimeType('application/x-tar');

        $compressA = $target->shouldBeCompressed();
        $exec      = $this->getExec($target);
        $compressB = $target->shouldBeCompressed();
        $cliResult = $this->execute($exec, $target, false);

        // maybe compression got deactivated because of an invalid compressor
        if ($compressA != $compressB) {
            $result->debug('deactivated compression');
        }
        $result->debug($cliResult->getCmd());

        if (!$cliResult->wasSuccessful()) {
            throw new Exception('tar failed');
        }

        return $result;
    }

    /**
     * Create the Exec to run the 'tar' command
     *
     * @param  \phpbu\Backup\Target $target
     * @return \phpbu\Backup\Cli\Exec
     */
    public function getExec(Target $target)
    {
        $exec = new Exec();
        $cmd  = new Cmd($this->binary);

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }

        // check if 'tar' can handle the requested compression
        if ($target->shouldBeCompressed()) {
            $name           = $target->getCompressor()->getCommand(false);
            $compressOption = $this->getCompressorOption($name);
            // the requested compression is not available for the 'tar' command
            if (!$compressOption) {
                $target->disableCompression();
            }
        } else {
            $compressOption = '';
        }

        $cmd->addOption(
            '-' . $compressOption . 'cf',
            array(
                $target->getPathname(true),
                $this->path,
            )
        );
        $exec->addCommand($cmd);

        return $exec;
    }

    /**
     * Return 'tar' compressor option e.g. 'j' for bzip2.
     *
     * @param  $compressor
     * @return string
     */
    protected function getCompressorOption($compressor)
    {
        return $this->isCompressorValid($compressor) ? $this->compressors[$compressor] : null;
    }

    /**
     * Return true if a given compressor is valid false otherwise.
     *
     * @param  string $compressor
     * @return boolean
     */
    protected function isCompressorValid($compressor)
    {
        return isset($this->compressors[$compressor]);
    }
}
