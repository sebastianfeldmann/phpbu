<?php
namespace phpbu\App\Backup;

use phpbu\App\Cli\Executable;

/**
 * Base class for Actions using cli tools.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
abstract class Cli
{
    /**
     * Command to execute
     *
     * @var \phpbu\App\Cli\Executable
     */
    protected $executable;

    /**
     * Exec setter, mostly for test purposes.
     *
     * @param \phpbu\App\Cli\Executable $exec
     */
    public function setExecutable(Executable $exec)
    {
        $this->executable = $exec;
    }

    /**
     * Executes the cli commands and handles compression
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Result
     * @throws \phpbu\App\Exception
     */
    protected function execute(Target $target)
    {
        return $this->getExecutable($target)->run();
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    abstract public function getExecutable(Target $target);
}
