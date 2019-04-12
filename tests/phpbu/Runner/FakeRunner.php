<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Backup\Source;

/**
 * Class FakeRunner
 *
 * Dummy class to test phpbu Factory create methods.
 */
class FakeRunner
{
    /**
     * Setup the source.
     *
     * @param array $conf
     */
    public function setup(array $conf = [])
    {
        // do something fooish
    }

    /**
     * Runner the backup
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result $result
     * @return \phpbu\App\Backup\Source\Status
     */
    public function backup(Target $target, Result $result)
    {
        return new Source\Status();
    }
}
