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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
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
            if (strpos($out, 'socket://') === 0) {
                $socket = explode(':', str_replace('socket://', '', $out));

                if (count($socket) != 2) {
                    throw new InvalidArgumentException(sprintf('Invalid socket: %s', $out));
                }
                $this->out = fsockopen($socket[0], $socket[1]);
            } else {
                if (strpos($out, 'php://') === false && !is_dir(dirname($out))) {
                    mkdir(dirname($out), 0777, true);
                }
                $this->out = fopen($out, 'wt');
            }
            $this->outTarget = $out;
        } else {
            $this->out = $out;
        }
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
