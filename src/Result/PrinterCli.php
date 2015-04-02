<?php
namespace phpbu\App\Result;

use InvalidArgumentException;
use phpbu\App\Event;
use phpbu\App\Listener;
use phpbu\App\Log\Printer;
use phpbu\App\Result;
use phpbu\App\Util\Str;
use phpbu\App\Version;
use PHP_Timer;
use SebastianBergmann\Environment\Console;

/**
 * Default app output.
 *
 * Heavily 'inspired' by Sebastian Bergmann's phpunit PHPUnit_TextUI_ResultPrinter.
 *
 * @package    phpbu
 * @subpackage Result
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class PrinterCli extends Printer implements Listener
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
     * Amount of executed crypts
     *
     * @var integer
     */
    private $numCrypts = 0;

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
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'phpbu.debug'           => 'onDebug',
            'phpbu.app_start'       => 'onPhpbuStart',
            'phpbu.backup_start'    => 'onBackupStart',
            'phpbu.backup_failed'   => 'onBackupFailed',
            'phpbu.backup_end'      => 'onBackupEnd',
            'phpbu.check_start'     => 'onCheckStart',
            'phpbu.check_failed'    => 'onCheckFailed',
            'phpbu.check_end'       => 'onCheckEnd',
            'phpbu.crypt_start'     => 'onCryptStart',
            'phpbu.crypt_skipped'   => 'onCryptSkipped',
            'phpbu.crypt_failed'    => 'onCryptFailed',
            'phpbu.crypt_end'       => 'onCryptEnd',
            'phpbu.sync_start'      => 'onSyncStart',
            'phpbu.sync_skipped'    => 'onSyncSkipped',
            'phpbu.sync_failed'     => 'onSyncFailed',
            'phpbu.sync_end'        => 'onSyncEnd',
            'phpbu.cleanup_start'   => 'onCleanupStart',
            'phpbu.cleanup_skipped' => 'onCleanupSkipped',
            'phpbu.cleanup_failed'  => 'onCleanupFailed',
            'phpbu.cleanup_end'     => 'onCleanupEnd',
            'phpbu.app_end'         => 'onPhpbuEnd',
        );
    }

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
     * @param \phpbu\App\Event\App\Start $event
     */
    public function onPhpbuStart(Event\App\Start $event)
    {
        $configuration = $event->getConfiguration();
        $this->write(
            Version::getVersionString() . PHP_EOL .
            PHP_EOL .
            'Configuration read from ' . $configuration->getPathname() . PHP_EOL .
            PHP_EOL
        );
    }

    /**
     * Backup start event.
     *
     * @param \phpbu\App\Event\Backup\Start $event
     */
    public function onBackupStart(Event\Backup\Start $event)
    {
        $backup = $event->getConfiguration();
        $this->numBackups++;
        if ($this->debug) {
            $this->write('create backup (' . $backup->getSource()->type . '): ');
        }
    }

    /**
     * Backup failed event.
     *
     * @param \phpbu\App\Event\Backup\Failed $event
     */
    public function onBackupFailed(Event\Backup\Failed $event)
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
     * @param \phpbu\App\Event\Backup\End $event
     */
    public function onBackupEnd(Event\Backup\End $event)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Check start event.
     *
     * @param \phpbu\App\Event\Check\Start $event
     */
    public function onCheckStart(Event\Check\Start $event)
    {
        $check = $event->getConfiguration();
        $this->numChecks++;
        if ($this->debug) {
            $this->write('check (' . $check->type . '): ');
        }
    }

    /**
     * Check failed event.
     *
     * @param \phpbu\App\Event\Check\Failed $event
     */
    public function onCheckFailed(Event\Check\Failed $event)
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
     * @param \phpbu\App\Event\Check\End $event
     */
    public function onCheckEnd(Event\Check\End $event)
    {
        if ($this->debug) {
            $this->write('passed' . PHP_EOL);
        }
    }

    /**
     * Crypt start event.
     *
     * @param \phpbu\App\Event\Crypt\Start $event
     */
    public function onCryptStart(Event\Crypt\Start $event)
    {
        $crypt = $event->getConfiguration();
        $this->numCrypts++;
        if ($this->debug) {
            $this->write('crypt (' . $crypt->type . '): ');
        }
    }

    /**
     * Crypt skipped event.
     *
     * @param \phpbu\App\Event\Crypt\Skipped $event
     */
    public function onCryptSkipped(Event\Crypt\Skipped $event)
    {
        if ($this->debug) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                'skipped'
            );
        }
    }

    /**
     * Crypt failed event.
     *
     * @param \phpbu\App\Event\Crypt\Failed $event
     */
    public function onCryptFailed(Event\Crypt\Failed $event)
    {
        if ($this->debug) {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'failed'
            );
        }
    }

    /**
     * Crypt end event.
     *
     * @param \phpbu\App\Event\Crypt\End $event
     */
    public function onCryptEnd(Event\Crypt\End $event)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Sync start event.
     *
     * @param \phpbu\App\Event\Sync\Start $event
     */
    public function onSyncStart(Event\Sync\Start $event)
    {
        $sync = $event->getConfiguration();
        $this->numSyncs++;
        if ($this->debug) {
            $this->write('sync start (' . $sync->type . '): ');
        }
    }

    /**
     * Sync skipped event.
     *
     * @param \phpbu\App\Event\Sync\Skipped $event
     */
    public function onSyncSkipped(Event\Sync\Skipped $event)
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
     * @param \phpbu\App\Event\Sync\Failed $event
     */
    public function onSyncFailed(Event\Sync\Failed $event)
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
     * @param \phpbu\App\Event\Sync\End $event
     */
    public function onSyncEnd(Event\Sync\End $event)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Cleanup start event.
     *
     * @param \phpbu\App\Event\Cleanup\Start $event
     */
    public function onCleanupStart(Event\Cleanup\Start $event)
    {
        $cleanup = $event->getConfiguration();
        $this->numCleanups++;
        if ($this->debug) {
            $this->write('cleanup start (' . $cleanup->type . '): ');
        }
    }

    /**
     * Cleanup skipped event.
     *
     * @param \phpbu\App\Event\Cleanup\Skipped $event
     */
    public function onCleanupSkipped(Event\Cleanup\Skipped $event)
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
     * @param \phpbu\App\Event\Cleanup\Failed $event
     */
    public function onCleanupFailed(Event\Cleanup\Failed $event)
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
     * @param \phpbu\App\Event\Cleanup\End $event
     */
    public function onCleanupEnd(Event\Cleanup\End $event)
    {
        if ($this->debug) {
            $this->write('done' . PHP_EOL);
        }
    }

    /**
     * Debugging.
     *
     * @param \phpbu\App\Event\Debug $event
     */
    public function onDebug(Event\Debug $event)
    {
        if ($this->debug) {
            $this->write($event->getMessage() . PHP_EOL);
        }
    }

    /**
     * phpbu end event.
     *
     * @param \phpbu\App\Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $result = $event->getResult();
        $this->printResult($result);
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
        /* @var $e \Exception */
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
        } elseif ($backup->okButSkipsOrFails()) {
                $this->writeWithColor(
                    'fg-black, bg-yellow',
                    'OK, but skipped or failed Crypts, Syncs or Cleanups!'
                );
        } else {
            $this->writeWithColor(
                'fg-white, bg-red, bold',
                'FAILED'
            );
        }
        $chExecuted = str_pad($backup->checkCount(), 8, ' ', STR_PAD_LEFT);
        $chFailed = str_pad($backup->checkCountFailed(), 6, ' ', STR_PAD_LEFT);
        $crExecuted = str_pad($backup->cryptCount(), 8, ' ', STR_PAD_LEFT);
        $crSkipped = str_pad($backup->cryptCountSkipped(), 7, ' ', STR_PAD_LEFT);
        $crFailed = str_pad($backup->cryptCountFailed(), 6, ' ', STR_PAD_LEFT);
        $syExecuted = str_pad($backup->syncCount(), 8, ' ', STR_PAD_LEFT);
        $sySkipped = str_pad($backup->syncCountSkipped(), 7, ' ', STR_PAD_LEFT);
        $syFailed = str_pad($backup->syncCountFailed(), 6, ' ', STR_PAD_LEFT);
        $clExecuted = str_pad($backup->cleanupCount(), 8, ' ', STR_PAD_LEFT);
        $clSkipped = str_pad($backup->cleanupCountSkipped(), 7, ' ', STR_PAD_LEFT);
        $clFailed = str_pad($backup->cleanupCountFailed(), 6, ' ', STR_PAD_LEFT);

        $out = PHP_EOL . '          | executed | skipped | failed |' . PHP_EOL
            . '----------+----------+---------+--------+' . PHP_EOL
            . ' checks   | ' . $chExecuted . ' |         | ' . $chFailed . ' |' . PHP_EOL
            . ' crypts   | ' . $crExecuted . ' | ' . $crSkipped . ' | ' . $crFailed . ' |' . PHP_EOL
            . ' syncs    | ' . $syExecuted . ' | ' . $sySkipped . ' | ' . $syFailed . ' |' . PHP_EOL
            . ' cleanups | ' . $clExecuted . ' | ' . $clSkipped . ' | ' . $clFailed . ' |' . PHP_EOL
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
                    'OK (%d %s, %d %s, %d %s, %d %s, %d %s)' . PHP_EOL,
                    count($result->getBackups()),
                    Str::appendPluralS('backup', count($result->getBackups())),
                    $this->numChecks,
                    Str::appendPluralS('check', $this->numChecks),
                    $this->numCrypts,
                    Str::appendPluralS('crypt', $this->numChecks),
                    $this->numSyncs,
                    Str::appendPluralS('sync', $this->numSyncs),
                    $this->numCleanups,
                    Str::appendPluralS('cleanup', $this->numCleanups)
                )
            );
        } elseif ($result->backupOkButSkipsOrFails()) {
            $this->writeWithColor(
                'fg-black, bg-yellow',
                sprintf(
                    "OK, but skipped|failed Crypts, Syncs or Cleanups!\n" .
                    'Backups: %d, Crypts: %d|%d, Syncs: %d|%d, Cleanups: %d|%d.' . PHP_EOL,
                    count($result->getBackups()),
                    $result->cryptsSkippedCount(),
                    $result->cryptsFailedCount(),
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
                    'Backups: %d, failed Checks: %d, failed Crypts: %d, failed Syncs: %d, failed Cleanups: %d.' . PHP_EOL,
                    count($result->getBackups()),
                    $result->checksFailedCount(),
                    $result->cryptsFailedCount(),
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
