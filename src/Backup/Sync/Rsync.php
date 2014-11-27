<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Result;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;

class Rsync implements Sync
{
    protected $config;

    public function setup(array $config)
    {
        $this->config = $config;
    }

    public function sync(Target $target, Result $result)
    {
        throw new Exception('NotImplementedException');
    }
}