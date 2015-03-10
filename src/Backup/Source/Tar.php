<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

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
     * Remove the packed data
     *
     * @var boolean
     */
    private $removeDir;

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
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupTar($conf);

        $this->showStdErr = Util\String::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->path       = Util\Arr::getValue($conf, 'path');
        $this->removeDir  = Util\String::toBoolean(Util\Arr::getValue($conf, 'removeDir', ''), false);

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
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        // set uncompressed default MIME type
        $target->setMimeType('application/x-tar');

        $compressA = $target->shouldBeCompressed();
        $exec      = $this->getExec($target);
        $compressB = $target->shouldBeCompressed();
        $tar       = $this->execute($exec, $target, false);

        // maybe compression got deactivated because of an invalid compressor
        if ($compressA != $compressB) {
            $result->debug('deactivated compression');
        }
        $result->debug($tar->getCmd());

        if (!$tar->wasSuccessful()) {
            throw new Exception('tar failed');
        }
        // delete the source data if requested
        if ($this->removeDir) {
            Util\Cli::removeDir($this->path);
        }

        return $result;
    }

    /**
     * Create the Exec to run the 'tar' command
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Cli\Exec
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $this->exec = new Exec();
            $cmd = new Cmd($this->binary);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $cmd->silence();
                // i kill you
            }

            // check if 'tar' can handle the requested compression
            if ($target->shouldBeCompressed()) {
                $name = $target->getCompressor()->getCommand(false);
                $compressOption = $this->getCompressorOption($name);
                // the requested compression is not available for the 'tar' command
                if (!$compressOption) {
                    $target->disableCompression();
                }
            } else {
                $compressOption = '';
            }

            $cmd->addOption('-' . $compressOption . 'cf');
            $cmd->addArgument($target->getPathname(true));
            $cmd->addOption('-C', $this->path, ' ');
            $cmd->addArgument('.');
            $this->exec->addCommand($cmd);
        }

        return $this->exec;
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
