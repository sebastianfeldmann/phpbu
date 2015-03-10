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
     * Silent setter
     *
     * @param boolean $bool
     */
    public function silence($bool = true)
    {
        $this->isSilent = $bool;
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
        if (is_array($argument)) {
            $argument        = array_map('escapeshellarg', $argument);
            $glue            = ' ';
            $escapedArgument = implode(' ', $argument);
        } else {
            $escapedArgument = escapeshellarg($argument);
        }
        $this->options[] = $option . (null !== $argument ? $glue . $escapedArgument : '');
    }

    /**
     * Add argument to list.
     *
     * @param mixed <string|array> $argument
     */
    public function addArgument($argument)
    {
        if (is_array($argument)) {
            $argument        = array_map('escapeshellarg', $argument);
            $escapedArgument = implode(' ', $argument);
        } else {
            $escapedArgument = escapeshellarg($argument);
        }
        $this->options[] = $escapedArgument;
    }

    /**
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name
            . ( count($this->options) ? ' ' . implode(' ', $this->options) : '' )
            . ( $this->isSilent       ? ' 2> /dev/null'                    : '' );
    }
}
