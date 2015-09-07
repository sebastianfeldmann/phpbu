<?php
namespace phpbu\App\Cli;

/**
 * Runner result
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
     * Output buffer.
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
    public function __construct($cmd, $code, array $output = array())
    {
        $this->cmd    = $cmd;
        $this->code   = $code;
        $this->buffer = $output;
    }

    /**
     * Cmd getter.
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
     * Command executed successful.
     */
    public function wasSuccessful()
    {
        return $this->code == 0;
    }

    /**
     * Output getter.
     *
     * @return mixed array
     */
    public function getOutput()
    {
        return $this->buffer;
    }

    /**
     * Return the output ins string format.
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
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getOutputAsString();
    }
}
