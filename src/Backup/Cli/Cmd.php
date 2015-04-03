<?php
namespace phpbu\App\Backup\Cli;

/**
 * Cli command
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Cmd
{
    /**
     * Command name
     *
     * @var string
     */
    private $name;

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
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Name getter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Silence the 'Cmd' by redirecting its stdErr output to /dev/null.
     * The silence feature is disabled for Windows systems.
     *
     * @param boolean $bool
     */
    public function silence($bool = true)
    {
        $this->isSilent = $bool && !defined('PHP_WINDOWS_VERSION_BUILD');
    }

    /**
     * Add option to list.
     *
     * @param string               $option
     * @param mixed <string|array> $argument
     * @param string               $glue
     */
    public function addOption($option, $argument = null, $glue = '=')
    {
        if ($argument !== null) {
            // for space for multiple argument list e.g. --option 'foo' 'bar'
            if (is_array($argument)) {
                $glue = ' ';
            }
            $argument = $glue . $this->escapeArgument($argument);
        } else {
            $argument = '';
        }
        $this->options[] = $option . $argument;
    }

    /**
     * Add argument to list.
     *
     * @param mixed <string|array> $argument
     */
    public function addArgument($argument)
    {
        $this->options[] = $this->escapeArgument($argument);
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
        return $this->name
            . (count($this->options) ? ' ' . implode(' ', $this->options) : '')
            . ($this->isSilent       ? ' 2> /dev/null'                    : '');
    }
}
