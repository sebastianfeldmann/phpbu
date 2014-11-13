<?php
namespace phpbu\Backup\Cli;

/**
 * Runner result
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Result
{
    /**
     * Command that got executed.
     *
     * @var string
     */
    private $cmd;

    /**
     * Result code
     *
     * @var integer
     */
    private $code;

    /**
     * Outputbuffer.
     *
     * @var array
     */
    private $buffer = array();

    /**
     * Text output
     *
     * @var string
     */
    private $output;

    /**
     * Constructor
     *
     * @param string  $cmd
     * @param integer $code
     * @param array   $output
     */
    public function __construct($cmd, $code, array $output)
    {
        $this->cmd    = $cmd;
        $this->code   = $code;
        $this->buffer = $output;
    }

    /**
     * Cmd getter
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Code getter.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Output getter
     *
     * @param  boolean $asString
     * @return mixed<array|string>
     */
    public function getOutput($asString = false)
    {
        if ($asString) {
            return $this->getOutputAsString();
        }
        return $this->buffer;
    }

    /**
     *
     * @return string
     */
    public function getOutputAsString()
    {
        if (null === $this->output) {
            $this->output = $this->bufferToText();
        }
        return $this->output;
    }

    /**
     * Converts the output buffer array into a string.
     *
     * @return string
     */
    private function bufferToText()
    {
        return implode(PHP_EOL, $this->buffer);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getOutputAsString();
    }
}
