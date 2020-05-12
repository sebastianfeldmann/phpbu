<?php
namespace phpbu\App\Log;

use phpbu\App\Exception;
use phpbu\App\Event;
use phpbu\App\Listener;

/**
 * Json Logger
 *
 * @package    phpbu
 * @subpackage Log
 * @author     MoeBrowne
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class Prometheus extends File implements Listener, Logger
{
    protected $backupStats = [];

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  - The method name to call (priority defaults to 0)
     *  - An array composed of the method name to call and the priority
     *  - An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'phpbu.backup_start'  => 'onBackupStart',
            'phpbu.backup_end'  => 'onBackupEnd',
            'phpbu.app_end'  => 'onPhpbuEnd',
        ];
    }

    /**
     * Setup the logger.
     *
     * @see    \phpbu\App\Log\Logger::setup
     * @param  array $options
     * @throws \phpbu\App\Exception
     */
    public function setup(array $options)
    {
        if (empty($options['target'])) {
            throw new Exception('no target given');
        }
        $this->setOut($options['target']);
    }

    /**
     * Backup start event.
     *
     * @param \phpbu\App\Event\Backup\Start $event
     */
    public function onBackupStart(Event\Backup\Start $event)
    {
        $this->backupStats[$event->getConfiguration()->getName()]['timeStart'] = microtime(true);
    }

    /**
     * Backup end event.
     *
     * @param \phpbu\App\Event\Backup\End $event
     */
    public function onBackupEnd(Event\Backup\End $event)
    {
        $this->backupStats[$event->getConfiguration()->getName()]['timeEnd'] = microtime(true);
        $this->backupStats[$event->getConfiguration()->getName()]['lastRun'] = microtime(true);
        $this->backupStats[$event->getConfiguration()->getName()]['size'] = $event->getTarget()->getSize();
    }

    /**
     * App end event.
     *
     * @param \phpbu\App\Event\App\End $event
     */
    public function onPhpbuEnd(Event\App\End $event)
    {
        $this->write('# HELP phpbu_backup_success Whether or not the backup succeeded' . PHP_EOL);
        $this->write('# TYPE phpbu_backup_success gauge' . PHP_EOL);
        foreach ($event->getResult()->getBackups() as $backupResult) {
            $this->write('phpbu_backup_success{name="' . $backupResult->getName() . '"} ' . (int)$backupResult->allOk() . PHP_EOL);
        }

        $this->write(PHP_EOL);

        $this->write('# HELP phpbu_backup_duration The total time the backup took to execute' . PHP_EOL);
        $this->write('# TYPE phpbu_backup_duration gauge' . PHP_EOL);
        foreach ($this->backupStats as $backupName => $backupStats) {
            $duration = $this->backupStats[$backupName]['timeEnd'] - $this->backupStats[$backupName]['timeStart'];
            $this->write('phpbu_backup_duration{name="' . $backupName . '"} ' . $duration . PHP_EOL);
        }

        $this->write(PHP_EOL);

        $this->write('# HELP phpbu_backup_last_run The unix timestamp of the last run' . PHP_EOL);
        $this->write('# TYPE phpbu_backup_last_run counter' . PHP_EOL);
        foreach ($this->backupStats as $backupName => $backupStats) {
            $this->write('phpbu_backup_last_run{name="' . $backupName . '"} ' . (int)$this->backupStats[$backupName]['lastRun'] . PHP_EOL);
        }

        $this->write(PHP_EOL);

        $this->write('# HELP phpbu_backup_size The size of the last successful backup' . PHP_EOL);
        $this->write('# TYPE phpbu_backup_size gauge' . PHP_EOL);
        foreach ($this->backupStats as $backupName => $backupStats) {
            $this->write('phpbu_backup_size{name="' . $backupName . '"} ' . $this->backupStats[$backupName]['size'] . PHP_EOL);
        }

    }
}
