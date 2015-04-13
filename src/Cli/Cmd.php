<?php
namespace phpbu\App\Cli;

/**
 * Cli command
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Cmd
{
    /**
     * Command name
     *
     * @var string
     */
    private $cmd;

    /**
     * Display stderr
     *
     * @var boolean
     */
    private $isSilent = false;

    /**
     * Command options
     *
     * @var array<string>
     */
    private $options = array();

    /**
     * Constructor.
     *
     * @param string $cmd
     */
    public function __construct($cmd)
    {
        $this->cmd = $cmd;
    }

    /**
     * Returns the string to execute on the command line.
     *
     * @return string
     */
    public function getCommandLine()
    {
        return $this->cmd
        . (count($this->options) ? ' ' . implode(' ', $this->options) : '')
        . ($this->isSilent       ? ' 2> /dev/null'                    : '');
    }

    /**
     * Silence the 'Cmd' by redirecting its stdErr output to /dev/null.
     * The silence feature is disabled for Windows systems.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Cmd
     */
    public function silence($bool = true)
    {
        $this->isSilent = $bool && !defined('PHP_WINDOWS_VERSION_BUILD');

        return $this;
    }

    /**
     * Add option to list.
     *
     * @param  string               $option
     * @param  mixed <string|array> $argument
     * @param  string               $glue
     * @return \phpbu\App\Cli\Cmd
     */
    public function addOption($option, $argument = null, $glue = '=')
    {
        if ($argument !== null) {
            // force space for multiple arguments e.g. --option 'foo' 'bar'
            if (is_array($argument)) {
                $glue = ' ';
            }
            $argument = $glue . $this->escapeArgument($argument);
        } else {
            $argument = '';
        }
        $this->options[] = $option . $argument;

        return $this;
    }

    /**
     * Adds an option to a command if it is not empty.
     *
     * @param string  $option
     * @param mixed   $check
     * @param boolean $asValue
     * @param string  $glue
     */
    public function addOptionIfNotEmpty($option, $check, $asValue = true, $glue = '=')
    {
        if (!empty($check)) {
            if ($asValue) {
                $this->addOption($option, $check, $glue);
            } else {
                $this->addOption($option);
            }
        }
    }

    /**
     * Add argument to list.
     *
     * @param  mixed <string|array> $argument
     * @return \phpbu\App\Cli\Cmd
     */
    public function addArgument($argument)
    {
        $this->options[] = $this->escapeArgument($argument);

        return $this;
    }

    /**
     * Escape a shell argument.
     *
     * @param  mixed <string|array> $argument
     * @return string
     */
    protected function escapeArgument($argument)
    {
        if (is_array($argument)) {
            $argument = array_map('escapeshellarg', $argument);
            $escaped  = implode(' ', $argument);
        } else {
            $escaped = escapeshellarg($argument);
        }
        return $escaped;
    }

    /**
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCommandLine();
    }
}
