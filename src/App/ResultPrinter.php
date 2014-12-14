<?php
namespace phpbu\App;

use InvalidArgumentException;
use phpbu\App\Listener;
use phpbu\App\Result;
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
     * Amount of executed backups
     *
     * @var integer
     */
    private $numBackups = 0;

    /**
     * Amount of executed checks
     *
     * @var integer
     */
    private $numChecks = 0;

    /**
     * Amount of executed Syncs
     *
     * @var integer
     */
    private $numSyncs = 0;

    /**
     * Amount of executed Cleanups
     *
     * @var integer
     */
    private $numCleanups = 0;

    /**
     * Constructor
     *
     * @param  string $out
     * @param  string $verbose
     * @param  string $colors
     * @param  string $debug
     * @throws \InvalidArgumentException
     */
    public function __construct($out = null, $verbose = false, $colors = false, $debug = false)
    {
        $this->setOut($out);

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
    public function phpbuEnd(Result $result)
    {
        // do something fooish
    }

    /**
     * @see \phpbu\App\Listener::backupStart()
     */
    public function backupStart($backup)
    {
        $this->numBackups++;
        if ($this->debug) {
            $this->write('create backup (' . $backup['source']['type'] . '): ');
        }
    }

    /**
     * @see \phpbu\App\Listener::backupFailed()
     */
    public function backupFailed($backup)
    {
        if ($this->debug) {
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
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::checkStart()
     */
    public function checkStart($check)
    {
        $this->numChecks++;
        if ($this->debug) {
            $this->write('check (' . $check['type'] . '): ');
        }
    }

    /**
     * @see \phpbu\App\Listener::checkFailed()
     */
    public function checkFailed($check)
    {
        if ($this->debug) {
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
        if ($this->debug) {
            $this->write('passed' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::syncStart()
     */
    public function syncStart($sync)
    {
        $this->numSyncs++;
        if ($this->debug) {
            $this->write('sync start (' . $sync['type'] . '): ');
        }
    }

    /**
     * @see \phpbu\App\Listener::syncSkipped()
     */
    public function syncSkipped($sync)
    {
        if ($this->debug) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'skipped'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::syncFailed()
     */
    public function syncFailed($sync)
    {
        if ($this->debug) {
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
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupStart()
     */
    public function cleanupStart($cleanup)
    {
        $this->numCleanups++;
        if ($this->debug) {
            $this->write('cleanup start (' . $cleanup['type'] . '): ');
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupSkipped()
     */
    public function cleanupSkipped($cleanup)
    {
        if ($this->debug) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'skipped'
            );
        }
    }

    /**
     * @see \phpbu\App\Listener::cleanupFailed()
     */
    public function cleanupFailed($cleanup)
    {
        if ($this->debug) {
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
        if ($this->debug) {
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
     *
     * @param \phpbu\App\Result $result
     */
    public function printResult(Result $result)
    {
        $this->printHeader();

        $this->printErrors($result);

        $backups = $result->getBackups();
        if ($this->verbose) {
            foreach ($backups as $backup) {
                $this->printBackupVerbose($backup);
            }
        }
        $this->printFooter($result);
    }

    /**
     * Prints the result header with memory usage info
     */
    protected function printHeader()
    {
        $this->write(PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL);
    }

    /**
     * Print Error informations
     *
     * @param \phpbu\App\Result $result
     */
    protected function printErrors(Result $result)
    {
        /* @var $e Exception */
        foreach ($result->getErrors() as $e) {
            $this->write(
                sprintf(
                    "Exception '%s' with message '%s'\nin %s:%d\n\n",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
    }

    /**
     * Prints verbose backup informations
     *
     * @param \phpbu\App\Result\Backup $backup
     */
    protected function printBackupVerbose(Result\Backup $backup)
    {
        $this->write(sprintf('backup %s: ', $backup->getName()));
        if ($backup->wasSuccessful() && $backup->noneSkipped() && $backup->noneFailed()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                'OK'
            );
        } elseif ((!$backup->noneSkipped() || !$backup->noneFailed()) && $backup->wasSuccessful()) {
                $this->writeWithColor(
                    'fg-black, bg-yellow',
                    'OK, but skipped or failed Syncs or Cleanups!'
                );
        } else {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'FAILED'
            );
        }
        $chExecuted = str_pad($backup->checkCount(), 8, ' ', STR_PAD_LEFT);
        $chFailed   = str_pad($backup->checkFailedCount(), 6, ' ', STR_PAD_LEFT);
        $syExecuted = str_pad($backup->syncCount(), 8, ' ', STR_PAD_LEFT);
        $sySkipped  = str_pad($backup->syncSkippedCount(), 7, ' ', STR_PAD_LEFT);
        $syFailed   = str_pad($backup->syncFailedCount(), 6, ' ', STR_PAD_LEFT);
        $clExecuted = str_pad($backup->cleanupCount(), 8, ' ', STR_PAD_LEFT);
        $clSkipped  = str_pad($backup->cleanupSkippedCount(), 7, ' ', STR_PAD_LEFT);
        $clFailed   = str_pad($backup->cleanupFailedCount(), 6, ' ', STR_PAD_LEFT);

        $out = '          | executed | skipped | failed |' . PHP_EOL
             . '----------+----------+---------+--------+' . PHP_EOL
             . ' checks   | ' . $chExecuted . ' |         | ' . $chFailed . ' |' . PHP_EOL
             . ' syncs    | ' . $syExecuted . ' | ' . $sySkipped . ' | ' . $syFailed . ' |' . PHP_EOL
             . ' cleanups | ' . $clExecuted . ' | ' . $clSkipped . ' | ' . $clFailed . ' |' . PHP_EOL
             . '----------+----------+---------+--------+' . PHP_EOL . PHP_EOL;

        $this->write($out);
    }

    /**
     * Prints 'OK' or 'FAILURE' footer
     *
     * @param Result $result
     */
    protected function printFooter(Result $result)
    {
        if (count($result->getBackups()) === 0) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'No backups executed!'
            );
        } elseif ($result->wasSuccessful() && $result->noneSkipped() && $result->noneFailed()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d backup%s, %d check%s, %d sync%s, %d cleanup%s)',
                    count($result->getBackups()),
                    (count($result->getBackups()) == 1) ? '' : 's',
                    $this->numChecks,
                    ($this->numChecks == 1) ? '' : 's',
                    $this->numSyncs,
                    ($this->numSyncs == 1) ? '' : 's',
                    $this->numCleanups,
                    ($this->numCleanups == 1) ? '' : 's'
                )
            );
        } elseif ((!$result->noneSkipped() || !$result->noneFailed()) && $result->wasSuccessful()) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                sprintf(
                    "OK, but skipped or failed Syncs or Cleanups!\n" .
                    'Backups: %d, Syncs: skipped|failed %d|%d, Cleanups: skipped|failed %d|%d.',
                    count($result->getBackups()),
                    $result->syncsSkippedCount(),
                    $result->syncsFailedCount(),
                    $result->cleanupsSkippedCount(),
                    $result->cleanupsFailedCount()
                )
            );
        } else {
            $this->writeWithColor(
                'fg-white, bg-red',
                sprintf(
                    "FAILURE!\n" .
                    'Backups: %d, failed Checks: %d, failed Syncs: %d, failed Cleanups: %d.',
                    count($result->getBackups()),
                    $result->checksFailedCount(),
                    $result->syncsFailedCount(),
                    $result->cleanupsFailedCount()
                )
            );
        }
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
