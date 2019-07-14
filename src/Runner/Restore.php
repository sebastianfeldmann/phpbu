<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Result;

/**
 * Restore Runner
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0
 * @internal
 */
class Restore extends Process
{
    /**
     * Execute backups.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     * @throws \Exception
     */
    public function run(Configuration $configuration) : Result
    {

    }
}
