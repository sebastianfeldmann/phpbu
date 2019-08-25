<?php
namespace phpbu\App\Log;

use InvalidArgumentException;

/**
 * Class that can write to a file or a socket.
 *
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class File
{
    /**
     * @var resource
     */
    protected $out;

    /**
     * @var string
     */
    protected $outTarget;

    /**
     * Set output target.
     *
     * @param mixed $out
     */
    public function setOut($out)
    {
        if (empty($out)) {
            throw new InvalidArgumentException('Out can\'t be empty');
        }
        if (is_string($out)) {
            $this->setupOut($out);
        } else {
            $this->out = $out;
        }
    }

    /**
     * Setup the out resource
     *
     * @param string $out
     */
    protected function setupOut(string $out)
    {
        if (strpos($out, 'php://') === false && !is_dir(dirname($out))) {
            mkdir(dirname($out), 0777, true);
        }
        $this->out       = fopen($out, 'wt');
        $this->outTarget = $out;
    }

    /**
     * @param string $buffer
     */
    public function write($buffer)
    {
        fwrite($this->out, $buffer);
    }

    /**
     * Close output if it's not to a php stream.
     */
    public function close()
    {
        if ($this->out && strncmp($this->outTarget, 'php://', 6) !== 0) {
            fclose($this->out);
        }
    }
}
