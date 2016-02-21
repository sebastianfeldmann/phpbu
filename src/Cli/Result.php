<?php
namespace phpbu\App\Cli;

/**
 * Executable result
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
    private $buffer;

    /**
     * StdOut
     *
     * @var string
     */
    private $stdOut;

    /**
     * StdErr
     *
     * @var string
     */
    private $stdErr;

    /**
     * Constructor
     *
     * @param string  $cmd
     * @param integer $code
     * @param string  $stdOut
     * @param string  $stdErr
     */
    public function __construct($cmd, $code, $stdOut = '', $stdErr = '')
    {
        $this->cmd    = $cmd;
        $this->code   = $code;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
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
     * StdOutput getter.
     *
     * @return mixed array
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * StdError getter.
     *
     * @return mixed array
     */
    public function getStdErr()
    {
        return $this->stdErr;
    }

    /**
     * Return the output ins string format.
     *
     * @return string
     */
    public function getStdOutAsArray()
    {
        if (null === $this->buffer) {
            $this->buffer = $this->textToBuffer();
        }
        return $this->buffer;
    }

    /**
     * Converts the output buffer array into a string.
     *
     * @return string
     */
    private function textToBuffer()
    {
        return explode(PHP_EOL, $this->stdOut);
    }

    /**
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->stdOut;
    }
}
