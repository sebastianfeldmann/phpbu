<?php
namespace phpbu\Backup\Runner\Cli;

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
     * Add option to list
     *
     * @param string $option
     */
    public function addOption($option)
    {
        $this->options[] = $option;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . ' ' . implode(' ', $this->options);
    }
}
