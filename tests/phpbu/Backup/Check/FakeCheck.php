<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Target;
use phpbu\App\Backup\Collector;
use phpbu\App\Result;

/**
 * Class FakeCheck
 *
 * Dummy class to test the phpbu factory and its check creation.
 */
class FakeCheck implements Check
{
    /**
     * Checks the created backup.
     *
     * @param  \phpbu\App\Backup\Target          $target
     * @param  string                            $value
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @param  \phpbu\App\Result                 $result
     * @return bool
     */
    public function pass(Target $target, $value, Collector\Local $collector, Result $result) : bool
    {
        // do something fooish
    }
}
