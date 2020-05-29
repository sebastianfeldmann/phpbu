<?php
namespace phpbu\App\Backup;

use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Result;
use phpbu\App\Configuration;
use phpbu\App\Util;
use SebastianFeldmann\Cli\Command\Runner;

/**
 * Base class for Actions using cli tools.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
abstract class Cli
{
    /**
     * Command runner to execute the executable.
     *
     * @var \SebastianFeldmann\Cli\Command\Runner
     */
    protected $runner;

    /**
     * Current timestamp
     *
     * @var int
     */
    protected $time;

    /**
     * Executable command.
     *
     * @var \phpbu\App\Cli\Executable
     */
    protected $executable;

    /**
     * Cli constructor.
     *
     * @param \SebastianFeldmann\Cli\Command\Runner $runner
     * @param int                                   $time
     */
    public function __construct(Runner $runner = null, $time = null)
    {
        $this->runner = $runner ? : new Runner\Simple();
        $this->time   = $time   ? : time();
    }

    /**
     * Executes the cli commands and handles compression
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Result
     * @throws \RuntimeException
     */
    protected function execute(Target $target) : Result
    {
        return $this->runCommand($this->getExecutable($target));
    }

    /**
     * Execute a cli command.
     *
     * @param  \phpbu\App\Cli\Executable $command
     * @return \phpbu\App\Cli\Result
     */
    protected function runCommand(Executable $command) : Result
    {
        $res = $this->runner->run($command);

        if (!$res->isSuccessful() && $res->isOutputRedirected()) {
            // remove file with errors
            $this->unlinkErrorFile($res->getRedirectPath());
        }

        return new Result($res->getCommandResult(), $command->getCommandPrintable());
    }

    /**
     * Remove file if it exists.
     *
     * @param string $file
     */
    public function unlinkErrorFile(string $file)
    {
        if (file_exists($file) && !is_dir($file)) {
            unlink($file);
        }
    }

    /**
     * Returns the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target) : Executable
    {
        if (null === $this->executable) {
            $this->executable = $this->createExecutable($target);
        }
        return $this->executable;
    }

    /**
     * Return an absolute path relative to the used file.
     *
     * @param  string $path
     * @param  string $default
     * @return string
     */
    protected function toAbsolutePath(string $path, string $default = '')
    {
        return !empty($path) ? Util\Path::toAbsolutePath($path, Configuration::getWorkingDirectory()) : $default;
    }

    /**
     * Creates the executable for this action.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    abstract protected function createExecutable(Target $target) : Executable;
}
