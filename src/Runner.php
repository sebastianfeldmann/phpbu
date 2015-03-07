<?php
namespace phpbu\App;

use phpbu\App\Backup;

/**
 * Runner actually executes all backup jobs.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Runner
{
    /**
     * Application output
     *
     * @var \phpbu\App\Listener
     */
    protected $printer;

    /**
     * Run all backups configured
     *
     * @param  array $arguments
     * @return \phpbu\App\Result
     */
    public function run(array $arguments)
    {
        $this->handleConfiguration($arguments);

        $this->printer = $this->createPrinter($arguments);
        $stop          = false;
        $result        = new Result();
        $result->addListener($this->printer);

        foreach ($this->createLoggers($arguments) as $listener) {
            $result->addListener($listener);
        }

        $result->phpbuStart($arguments);

        // create backups
        foreach ($arguments['backups'] as $backup) {
            if ($stop) {
                break;
            }
            // create target
            $checkFailed = false;
            $syncFailed  = false;
            $target      = new Backup\Target($backup['target']['dirname'], $backup['target']['filename']);
            $target->setupPath();
            // compressor
            if (!empty($backup['target']['compress'])) {
                $compressor = Backup\Compressor::create($backup['target']['compress']);
                $target->setCompressor($compressor);
            }
            /*
             *      __               __
             *     / /_  ____ ______/ /____  ______
             *    / __ \/ __ `/ ___/ //_/ / / / __ \
             *   / /_/ / /_/ / /__/ ,< / /_/ / /_/ /
             *  /_.___/\__,_/\___/_/|_|\__,_/ .___/
             *                             /_/
             */
            try {
                $result->backupStart($backup);
                $source = Factory::createSource($backup['source']['type'], $backup['source']['options']);
                $source->backup($target, $result);
                $result->backupEnd($backup);

                // setup the collector for this backup
                $collector = new Backup\Collector($target);

                /*
                 *          __              __
                 *    _____/ /_  ___  _____/ /_______
                 *   / ___/ __ \/ _ \/ ___/ //_/ ___/
                 *  / /__/ / / /  __/ /__/ ,< (__  )
                 *  \___/_/ /_/\___/\___/_/|_/____/
                 *
                 */
                foreach ($backup['checks'] as $check) {
                    try {
                        $result->checkStart($check);
                        $c = Factory::createCheck($check['type']);
                        if ($c->pass($target, $check['value'], $collector, $result)) {
                            $result->checkEnd($check);
                        } else {
                            $checkFailed = true;
                            $result->checkFailed($check);
                        }
                    } catch (Backup\Check\Exception $e) {
                        $checkFailed = true;
                        $result->addError($e);
                        $result->checkFailed($check);
                    }
                }

                /*
                 *     _______  ______  __________
                 *    / ___/ / / / __ \/ ___/ ___/
                 *   (__  ) /_/ / / / / /__(__  )
                 *  /____/\__, /_/ /_/\___/____/
                 *       /____/
                 */
                foreach ($backup['syncs'] as $sync) {
                    try {
                        $result->syncStart($sync);
                        if ($checkFailed && $sync['skipOnCheckFail']) {
                            $result->syncSkipped($sync);
                        } else {
                            $s = Factory::createSync($sync['type'], $sync['options']);
                            $s->sync($target, $result);
                            $result->syncEnd($sync);
                        }
                    } catch (Backup\Sync\Exception $e) {
                        $syncFailed = true;
                        $result->addError($e);
                        $result->syncFailed($sync);
                    }
                }

                /*
                 *          __
                 *    _____/ /__  ____ _____  __  ______
                 *   / ___/ / _ \/ __ `/ __ \/ / / / __ \
                 *  / /__/ /  __/ /_/ / / / / /_/ / /_/ /
                 *  \___/_/\___/\__,_/_/ /_/\__,_/ .___/
                 *                              /_/
                 */
                if (!empty($backup['cleanup'])) {
                    $cleanup = $backup['cleanup'];
                    try {
                        $result->cleanupStart($cleanup);
                        if (($checkFailed && $cleanup['skipOnCheckFail'])
                         || ($syncFailed && $cleanup['skipOnSyncFail'])) {
                            $result->cleanupSkipped($cleanup);
                        } else {
                            $cleaner = Factory::createCleaner($cleanup['type'], $cleanup['options']);
                            $cleaner->cleanup($target, $collector, $result);
                            $result->cleanupEnd($cleanup);
                        }
                    } catch (Backup\Cleaner\Exception $e) {
                        $result->debug('exception: ' . $e->getMessage());
                        $result->addError($e);
                        $result->cleanupFailed($cleanup);
                    }
                }

            } catch (\Exception $e) {
                $result->debug('exception: ' . $e->getMessage());
                $result->addError($e);
                $result->backupFailed($backup);
                if (true == $backup['stopOnError']) {
                    $stop = true;
                }
            }
        }

        $result->phpbuEnd();

        $this->printer->printResult($result);

        return $result;
    }

    /**
     * Make sure the config is valid.
     *
     * @param array $arguments
     */
    protected function handleConfiguration(array &$arguments)
    {
        $arguments['colors']  = isset($arguments['colors'])  ? $arguments['colors']  : false;
        $arguments['debug']   = isset($arguments['debug'])   ? $arguments['debug']   : false;
        $arguments['verbose'] = isset($arguments['verbose']) ? $arguments['verbose'] : false;
    }

    /**
     * Creates the output printer.
     *
     * @param  array $arguments
     * @return \phpbu\App\Result\PrinterCli
     */
    protected function createPrinter(array $arguments)
    {
        $printer = new Result\PrinterCli(
            isset($arguments['stderr']) ? 'php://stderr' : null,
            $arguments['verbose'],
            $arguments['colors'],
            $arguments['debug']
        );

        return $printer;
    }

    /**
     * Create all configured loggers.
     *
     * @param  array $arguments
     * @return array<Logger>
     */
    protected function createLoggers(array $arguments)
    {
        $loggers = array();
        foreach ($arguments['logging'] as $log) {
            $logger    = Factory::createLogger($log['type'], $log['options']);
            $loggers[] = $logger;
        }
        return $loggers;
    }
}
