<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Result;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;

class Rsync implements Sync
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $config;

    /**
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::setup()
     */
    public function setup(array $config)
    {
        $this->config = $config;
    }

    /**
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result)
    {
        throw new Exception('NotImplementedException');
        $rsync = new Cmd('rsync');

        if (isset($this->config['args'])) {
            $exec = 'rsync ' . $args;
        } else {
            // use archive mode, verbose and compress if not allready done
            $options = '-av' . $target->shouldBeCompressed() ? '' : 'z';
            $rsync->addOption($options);
            // sync folder
            // --delete

            // add target as source
            $rsync->addOption($target->getFilenameCompressed());

            $syncTarget = '';

            // remote user
            if (isset($this->config['user'])) {
                $syncTarget .= $this->config['user'] . '@';
            }

            // remote host
            if (isset($this->config['host'])) {
                $syncTarget .= $this->config['host'] . ':';
            }

            // remote path
            if (isset($this->config['path'])) {
                $syncTarget .= $this->config['path'];
            }

        }
    }
}
