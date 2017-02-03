<?php
namespace phpbu\App\Cli;

use SebastianFeldmann\Cli\Command\Result as CommandResult;

/**
 * Result
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Result
{
    /**
     * Result of executed command.
     *
     * @var \SebastianFeldmann\Cli\Command\Result
     */
    private $cmdResult;

    /**
     * Command print safe.
     *
     * @var string
     */
    private $printableCmd;

    /**
     * Result constructor.
     *
     * @param \SebastianFeldmann\Cli\Command\Result $cmdResult
     * @param string                                $cmdPrintable
     */
    public function __construct(CommandResult $cmdResult, string $cmdPrintable = '')
    {
        $this->cmdResult    = $cmdResult;
        $this->printableCmd = $cmdPrintable;
    }

    /**
     * Get the raw command result.
     *
     * @return \SebastianFeldmann\Cli\Command\Result
     */
    public function getCommandResult() : CommandResult
    {
        return $this->cmdResult;
    }

    /**
     * Return true if command execution was successful.
     *
     * @return bool
     */
    public function isSuccessful() : bool
    {
        return $this->cmdResult->isSuccessful();
    }

    /**
     * Return the executed cli command.
     *
     * @return string
     */
    public function getCmd() : string
    {
        return $this->cmdResult->getCmd();
    }

    /**
     * Return the executed cli command.
     *
     * @return string
     */
    public function getCmdPrintable() : string
    {
        return $this->printableCmd;
    }

    /**
     * Return commands output to stdOut.
     *
     * @return string
     */
    public function getStdOut() : string
    {
        return $this->cmdResult->getStdOut();
    }

    /**
     * Return commands error output to stdErr.
     *
     * @return string
     */
    public function getStdErr() : string
    {
        return $this->cmdResult->getStdErr();
    }

    /**
     * Is the output redirected to a file.
     *
     * @return bool
     */
    public function isOutputRedirected() : bool
    {
        return $this->cmdResult->isOutputRedirected();
    }

    /**
     * Return path to the file where the output is redirected to.
     *
     * @return string
     */
    public function getRedirectPath() : string
    {
        return $this->cmdResult->getRedirectPath();
    }

    /**
     * Return cmd output as array.
     *
     * @return array
     */
    public function getBufferedOutput() : array
    {
        return $this->cmdResult->getStdOutAsArray();
    }
}
