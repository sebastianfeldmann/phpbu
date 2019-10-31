<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

interface CleanableSyncStore
{
    public function createCollector(Target $target): Collector;
}