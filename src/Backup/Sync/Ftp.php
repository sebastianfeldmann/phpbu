<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;

class Ftp implements Sync
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
