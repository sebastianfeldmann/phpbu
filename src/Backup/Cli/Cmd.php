<?php
namespace phpbu\Backup\Cli;

/**
 * Cli command
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
     * Constructor
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
     * Add option to list
     *
     * @param string              $option
     * @param miyed<string|array> $argument
     * @param string              $glue
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
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . ' ' . implode(' ', $this->options) . ( $this->isSilent ? ' 2> /dev/null' : '');
    }
}
