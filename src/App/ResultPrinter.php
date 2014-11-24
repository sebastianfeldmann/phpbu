<?php
namespace phpbu\App;

use InvalidArgumentException;
use phpbu\App\Listener;
use phpbu\App\Version;
use phpbu\Log\Printer;
use PHP_Timer;
use SebastianBergmann\Environment\Console;

/**
 * Default app output.
 *
 * Heavily 'inspired' by Sebastian Bergmann's phpunit PHPUnit_TextUI_ResultPrinter.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class ResultPrinter extends Printer implements Listener
{
    /**
     * Verbose output
     *
     * @var boolean
     */
    protected $verbose;

    /**
     * Output with colors
     *
     * @var boolean
     */
    protected $colors;

    /**
     * Is debug active
     *
     * @var boolean
     */
    protected $debug;

    /**
     * List of console color codes.
     *
     * @var array
     */
    private static $ansiCodes = array(
        'bold'       => 1,
        'fg-black'   => 30,
        'fg-red'     => 31,
        'fg-yellow'  => 33,
        'fg-cyan'    => 36,
        'fg-white'   => 37,
        'bg-red'     => 41,
        'bg-green'   => 42,
        'bg-yellow'  => 43
    );

    /**
     * Constructor
     *
     * @param  string                   $out
     * @param  string                   $verbose
     * @param  string                   $colors
     * @param  string                   $debug
     * @throws \InvalidArgumentException
     */
    public function __construct($out = null, $verbose = false, $colors = false, $debug = false)
    {
        parent::__construct($out);

        if (is_bool($verbose)) {
            $this->verbose = $verbose;
        } else {
            throw new InvalidArgumentException('Expected $verbose to be of type boolean');
        }

        if (is_bool($colors)) {
            $console      = new Console;
            $this->colors = $colors && $console->hasColorSupport();
        } else {
            throw new InvalidArgumentException('Expected $colors to be of type boolean');
        }

        if (is_bool($debug)) {
            $this->debug = $debug;
        } else {
            throw new InvalidArgumentException('Expected $debug to be of type boolean');
        }
    }

    /**
     * @see \phpbu\App\Listener::phpbuStart()
     */
    public function phpbuStart($settings)
    {
        $this->write(
            Version::getVersionString() . PHP_EOL .
            PHP_EOL .
            'Configuration read from ' . $settings['configuration'] . PHP_EOL .
            PHP_EOL
        );
    }

    /**
     * @see \phpbu\App\Listener::phpbuEnd()
     */
    public function phpbuEnd()
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::backupStart()
     */
    public function backupStart($backup)
    {
        if ($this->verbose) {
            $this->write('create backup (' . $backup['source']['type'] . '):' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::backupFailed()
     */
    public function backupFailed($backup)
    {
        if ($this->verbose) {
            $this->write(PHP_EOL);
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'failed'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::backupEnd()
     */
    public function backupEnd($backup)
    {
        if ($this->verbose) {
            $this->write('backup done' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::checkStart()
     */
    public function checkStart($check)
    {
        if ($this->verbose) {
            $this->write('check (' . $check['type'] . '): ');
        }
    }

    /**
     * @see \phpbu\App\Listener::checkFailed()
     */
    public function checkFailed($check)
    {
        if ($this->verbose) {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'failed'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::checkEnd()
     */
    public function checkEnd($check)
    {
        if ($this->verbose) {
            $this->write('passed' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::syncStart()
     */
    public function syncStart($sync)
    {
        if ($this->verbose) {
            $this->write('sync start: ');
        }
    }

    /**
     * @see \phpbu\App\Listener::syncFailed()
     */
    public function syncFailed($sync)
    {
        if ($this->verbose) {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'failed'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::syncEnd()
     */
    public function syncEnd($sync)
    {
        if ($this->verbose) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupStart()
     */
    public function cleanupStart($cleanup)
    {
        if ($this->verbose) {
            $this->write('cleanup start: ');
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupFailed()
     */
    public function cleanupFailed($cleanup)
    {
        if ($this->verbose) {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'failed'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupEnd()
     */
    public function cleanupEnd($cleanup)
    {
        if ($this->verbose) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::debug()
     */
    public function debug($msg)
    {
        if ($this->debug) {
            $this->write($msg . PHP_EOL);
        }
    }

    /**
     * Prints a result summary
     */
    public function printResult()
    {
        // TODO:
        // backup count
        // check count
        // sync count
        // cleanup count
        print PHP_Timer::resourceUsage() . PHP_EOL;
    }

    /**
     * Formats a buffer with a specified ANSI color sequence if colors are enabled.
     *
     * @author Sebastian Bergmann <sebastian@phpunit.de>
     * @param  string $color
     * @param  string $buffer
     * @return string
     */
    protected function formatWithColor($color, $buffer)
    {
        if (!$this->colors) {
            return $buffer;
        }

        $codes   = array_map('trim', explode(',', $color));
        $lines   = explode("\n", $buffer);
        $padding = max(array_map('strlen', $lines));

        $styles = array();
        foreach ($codes as $code) {
            $styles[] = self::$ansiCodes[$code];
        }
        $style = sprintf("\x1b[%sm", implode(';', $styles));

        $styledLines = array();
        foreach ($lines as $line) {
            $styledLines[] = $style . str_pad($line, $padding) . "\x1b[0m";
        }

        return implode(PHP_EOL, $styledLines);
    }

    /**
     * Writes a buffer out with a color sequence if colors are enabled.
     *
     * @author Sebastian Bergmann <sebastian@phpunit.de>
     * @param  string $color
     * @param  string $buffer
     */
    protected function writeWithColor($color, $buffer)
    {
        $buffer = $this->formatWithColor($color, $buffer);
        $this->write($buffer . PHP_EOL);
    }
}
