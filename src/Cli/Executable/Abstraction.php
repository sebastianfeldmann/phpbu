<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Util\Cli;
use SebastianFeldmann\Cli\CommandLine;

/**
 * Execute Binary
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
abstract class Abstraction
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $cmd;

    /**
     * Absolute path to command.
     *
     * @var string
     */
    protected $binary;

    /**
     * Command to execute
     *
     * @var \SebastianFeldmann\Cli\CommandLine
     */
    protected $commandLine;

    /**
     * Setup binary.
     *
     * @param string $cmd
     * @param string $path
     */
    protected function setup(string $cmd, string $path = '') {
        $this->cmd    = $cmd;
        $this->binary = Cli::detectCmdLocation($cmd, $path, Cli::getCommandLocations($this->cmd));
    }

    /**
     * Returns the CommandLine for this command.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     */
    public function getCommandLine() : CommandLine
    {
        return $this->createCommandLine();
    }

    /**
     * CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     */
    abstract protected function createCommandLine() : CommandLine;

    /**
     * Return the command line to execute.
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getCommand() : string
    {
        return $this->getCommandLine()->getCommand();
    }

    /**
     * Return the command with masked passwords or keys.
     *
     * By default just return the original command. Subclasses with password
     * arguments have to override this method.
     *
     * @return string
     */
    public function getCommandPrintable() : string
    {
        return $this->getCommand();
    }
}
