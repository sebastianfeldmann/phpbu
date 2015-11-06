<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Check as CheckExe;
use phpbu\App\Backup\Check\Exception;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration\Backup\Check as CheckConfig;
use phpbu\App\Result;

/**
 * Check Runner class.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Check
{
    /**
     * Failure state of all executed Checks.
     *
     * @var boolean
     */
    private $failure = false;

    /**
     * Executes a backup check.
     *
     * @param \phpbu\App\Backup\Check               $check
     * @param \phpbu\App\Configuration\Backup\Check $config
     * @param \phpbu\App\Backup\Target              $target
     * @param \phpbu\App\Backup\Collector           $collector
     * @param \phpbu\App\Result                     $result
     */
    public function run(CheckExe $check, CheckConfig $config, Target $target, Collector $collector, Result $result)
    {
        try {
            $result->checkStart($config);

            if ($check->pass($target, $config->value, $collector, $result)) {
                $result->checkEnd($config);
            } else {
                $this->failure = true;
                $result->checkFailed($config);
            }
        } catch (Exception $e) {
            $this->failure = true;
            $result->addError($e);
            $result->checkFailed($config);
        }
    }

    /**
     * Return true if the last check did fail.
     *
     * @return boolean
     */
    public function hasFailed()
    {
        return $this->failure;
    }
}
