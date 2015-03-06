<?php
namespace phpbu\App;

use InvalidArgumentException;
use phpbu\App\Result;
use phpbu\Log\Printer;
use phpbu\Util\String;
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
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
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
     * @param  string  $out
     * @param  boolean $verbose
     * @param  boolean $colors
     * @param  boolean $debug
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
     * phpbu start event.
     *
     * @see   \phpbu\App\Listener::phpbuStart()
     * @param array $settings
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
     * phpbu end event.
     *
     * @see   \phpbu\App\Listener::phpbuEnd()
     * @param \phpbu\App\Result $result
     */
    public function phpbuEnd(Result $result)
    {
        // do something fooish
    }

    /**
     * Backup start event.
     *
     * @see   \phpbu\App\Listener::backupStart()
     * @param array $backup
     */
    public function backupStart($backup)
    {
        $this->numBackups++;
        if ($this->debug) {
            $this->write('create backup (' . $backup['source']['type'] . '): ');
        }
    }

    /**
     * Backup failed event.
     *
     * @see   \phpbu\App\Listener::backupFailed()
     * @param array $backup
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
     * Backup end event.
     *
     * @see   \phpbu\App\Listener::backupEnd()
     * @param array $backup
     */
    public function backupEnd($backup)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Check start event.
     *
     * @see   \phpbu\App\Listener::checkStart()
     * @param array $check
     */
    public function checkStart($check)
    {
        $this->numChecks++;
        if ($this->debug) {
            $this->write('check (' . $check['type'] . '): ');
        }
    }

    /**
     * Check failed event.
     *
     * @see   \phpbu\App\Listener::checkFailed()
     * @param array $check
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
     * Check end event.
     *
     * @see   \phpbu\App\Listener::checkEnd()
     * @param array $check
     */
    public function checkEnd($check)
    {
        if ($this->debug) {
            $this->write('passed' . PHP_EOL);
        }
    }

    /**
     * Sync start event.
     *
     * @see   \phpbu\App\Listener::syncStart()
     * @param array $sync
     */
    public function syncStart($sync)
    {
        $this->numSyncs++;
        if ($this->debug) {
            $this->write('sync start (' . $sync['type'] . '): ');
        }
    }

    /**
     * Sync skipped event.
     *
     * @see   \phpbu\App\Listener::syncSkipped()
     * @param array $sync
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
     * Sync failed event.
     *
     * @see   \phpbu\App\Listener::syncFailed()
     * @param array $sync
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
     * Sync end event.
     *
     * @see   \phpbu\App\Listener::syncEnd()
     * @param array $sync
     */
    public function syncEnd($sync)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Cleanup start event.
     *
     * @see   \phpbu\App\Listener::cleanupStart()
     * @param array $cleanup
     */
    public function cleanupStart($cleanup)
    {
        $this->numCleanups++;
        if ($this->debug) {
            $this->write('cleanup start (' . $cleanup['type'] . '): ');
        }
    }

    /**
     * Cleanup skipped event.
     *
     * @see   \phpbu\App\Listener::cleanupSkipped()
     * @param array $cleanup
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
     * Cleanup failed event.
     *
     * @see   \phpbu\App\Listener::cleanupFailed()
     * @param array $cleanup
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
     * Cleanup end event.
     *
     * @see   \phpbu\App\Listener::cleanupEnd()
     * @param array $cleanup
     */
    public function cleanupEnd($cleanup)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Debugging.
     *
     * @see   \phpbu\App\Listener::debug()
     * @param string $msg
     */
    public function debug($msg)
    {
        if ($this->debug) {
            $this->write($msg . PHP_EOL);
        }
    }

    /**
     * Prints a result summary.
     *
     * @param \phpbu\App\Result $result
     */
    public function printResult(Result $result)
    {
        $this->printHeader();
        $this->printErrors($result);

        if ($this->verbose) {
            foreach ($result->getBackups() as $backup) {
                $this->printBackupVerbose($backup);
            }
        }
        $this->printFooter($result);
    }

    /**
     * Prints the result header with memory usage info.
     */
    protected function printHeader()
    {
        $this->write(PHP_Timer::resourceUsage() . PHP_EOL . PHP_EOL);
    }

    /**
     * Print error information.
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
     * Prints verbose backup information.
     *
     * @param \phpbu\App\Result\Backup $backup
     */
    protected function printBackupVerbose(Result\Backup $backup)
    {
        $this->write(sprintf('backup %s: ', $backup->getName()));
        if ($backup->allOk()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                'OK'
            );
        } elseif (($backup->okButSkipsOrFails())) {
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

        $exec = String::padAll(
            array(
                'ch' => $backup->checkCount(),
                'sy' => $backup->syncCount(),
                'cl' => $backup->cleanupCount(),
            ),
            8
        );
        $fail = String::padAll(
            array(
                'ch' => $backup->checkCountFailed(),
                'sy' => $backup->syncCountFailed(),
                'cl' => $backup->cleanupCountFailed(),
            ),
            6
        );
        $skip = String::padAll(
            array(
                'sy' => $backup->syncCountSkipped(),
                'cl' => $backup->cleanupCountSkipped(),
            ),
            7
        );

        $out = '          | executed | skipped | failed |' . PHP_EOL
             . '----------+----------+---------+--------+' . PHP_EOL
             . ' checks   | ' . $exec['ch'] . ' |         | ' . $fail['ch'] . ' |' . PHP_EOL
             . ' syncs    | ' . $exec['ch'] . ' | ' . $skip['sy'] . ' | ' . $fail['sy'] . ' |' . PHP_EOL
             . ' cleanups | ' . $exec['ch'] . ' | ' . $skip['cl'] . ' | ' . $fail['cl'] . ' |' . PHP_EOL
             . '----------+----------+---------+--------+' . PHP_EOL . PHP_EOL;

        $this->write($out);
    }

    /**
     * Prints 'OK' or 'FAILURE' footer.
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
        } elseif ($result->allOk()) {
            $this->writeWithColor(
                'fg-black, bg-green',
                sprintf(
                    'OK (%d %s, %d %s, %d %s, %d %s)',
                    count($result->getBackups()),
                    String::appendPluralS('backup', count($result->getBackups())),
                    $this->numChecks,
                    String::appendPluralS('check', $this->numChecks),
                    $this->numSyncs,
                    String::appendPluralS('sync', $this->numSyncs),
                    $this->numCleanups,
                    String::appendPluralS('cleanup', $this->numCleanups)
                )
            );
        } elseif ($result->backupOkButSkipsOrFails()) {
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
