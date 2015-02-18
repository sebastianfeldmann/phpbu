<?php
namespace phpbu\Log;

use InvalidArgumentException;

/**
 * Utility class that can print to STDOUT or write to a file.
 *
 * This is a minimal adjusted clone of Sebastian Bergmann's PHPUnit_Util_Printer.
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 */
class Printer
{
    /**
     * If true, flush output after every write.
     *
     * @var boolean
     */
    protected $autoFlush = false;

    /**
     * @var resource
     */
    protected $out;

    /**
     * @var string
     */
    protected $outTarget;

    /**
     * @var boolean
     */
    protected $printsHTML = false;

    /**
     * Constructor
     *
     * @param string $out
     */
    public function __construct($out = null)
    {
        $this->setOut($out);
    }

    /**
     * Out setter.
     *
     * @param  mixed $out
     * @throws InvalidArgumentException
     */
    protected function setOut($out = null)
    {
        if ($out !== null) {
            if (is_string($out)) {
                if (strpos($out, 'socket://') === 0) {
                    $out = explode(':', str_replace('socket://', '', $out));

                    if (sizeof($out) != 2) {
                        throw new InvalidArgumentException(sprintf('Invalid socket: %s', $out));
                    }
                    $this->out = fsockopen($out[0], $out[1]);
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
    }

    /**
     * Flush buffer, optionally tidy up HTML, and close output if it's not to a php stream
     */
    public function flush()
    {
        if ($this->out && strncmp($this->outTarget, 'php://', 6) !== 0) {
            fclose($this->out);
        }

        if ($this->printsHTML === true &&
            $this->outTarget !== null &&
            strpos($this->outTarget, 'php://') !== 0 &&
            strpos($this->outTarget, 'socket://') !== 0 &&
            extension_loaded('tidy')) {
            file_put_contents(
                $this->outTarget,
                tidy_repair_file(
                    $this->outTarget,
                    array('indent' => true, 'wrap' => 0),
                    'utf8'
                )
            );
        }
    }

    /**
     * Performs a safe, incremental flush.
     *
     * Do not confuse this function with the flush() function of this class,
     * since the flush() function may close the file being written to, rendering
     * the current object no longer usable.
     */
    public function incrementalFlush()
    {
        if ($this->out) {
            fflush($this->out);
        } else {
            flush();
        }
    }

    /**
     * @param string $buffer
     */
    public function write($buffer)
    {
        if ($this->out) {
            fwrite($this->out, $buffer);

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (PHP_SAPI != 'cli') {
                $buffer = htmlspecialchars($buffer);
            }
            print $buffer;

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    /**
     * Check auto-flush mode.
     *
     * @return boolean
     */
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }

    /**
     * Set auto-flushing mode.
     *
     * If set, *incremental* flushes will be done after each write. This should
     * not be confused with the different effects of this class' flush() method.
     *
     * @param boolean $autoFlush
     */
    public function setAutoFlush($autoFlush)
    {
        if (is_bool($autoFlush)) {
            $this->autoFlush = $autoFlush;
        } else {
            throw new InvalidArgumentException('Invalid argument, boolean expected');
        }
    }
}
