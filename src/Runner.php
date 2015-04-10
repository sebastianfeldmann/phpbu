<?php
namespace phpbu\App;

use phpbu\App\Backup;
use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Source\Status;
use phpbu\App\Backup\Target;

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
     * Application result
     *
     * @var \phpbu\App\Result
     */
    protected $result;

    /**
     * Backup failed
     *
     * @var boolean
     */
    protected $failure;

    /**
     * Run phpbu
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     */
    public function run(Configuration $configuration)
    {
        Util\Cli::registerBase('configuration', $configuration->getPath());
        $this->handleIniSettings($configuration);
        $this->handleIncludePath($configuration);
        $this->handleBootstrap($configuration);

        $this->printer = $this->createPrinter($configuration);
        $stop          = false;
        $this->result  = new Result();
        $this->result->addListener($this->printer);

        $this->setupLoggers($configuration);

        $this->result->phpbuStart($configuration);

        // create backups
        /** @var \phpbu\App\Configuration\Backup $backup */
        foreach ($configuration->getBackups() as $backup) {
            if ($stop) {
                break;
            }
            // setup target and collector, reset failure state
            $target        = $this->createTarget($backup->getTarget());
            $collector     = new Collector($target);
            $this->failure = false;

            try {
                /*      ___  ___  _______ ____  _____
                 *     / _ )/ _ |/ ___/ //_/ / / / _ \
                 *    / _  / __ / /__/ ,< / /_/ / ___/
                 *   /____/_/ |_\___/_/|_|\____/_/
                 */
                $this->executeBackup($backup, $target);

                /*     _______ _____________ ______
                 *    / ___/ // / __/ ___/ //_/ __/
                 *   / /__/ _  / _// /__/ ,< _\ \
                 *   \___/_//_/___/\___/_/|_/___/
                 */
                $this->executeChecks($backup, $target, $collector);

                /*     __________  _____  ______
                 *    / ___/ _ \ \/ / _ \/_  __/
                 *   / /__/ , _/\  / ___/ / /
                 *   \___/_/|_| /_/_/    /_/
                 */
                $this->executeCrypt($backup, $target);

                /*      ______  ___  ___________
                 *     / __/\ \/ / |/ / ___/ __/
                 *    _\ \   \  /    / /___\ \
                 *   /___/   /_/_/|_/\___/___/
                 */
                $this->executeSyncs($backup, $target);

                /*     _______   _______   _  ____  _____
                 *    / ___/ /  / __/ _ | / |/ / / / / _ \
                 *   / /__/ /__/ _// __ |/    / /_/ / ___/
                 *   \___/____/___/_/ |_/_/|_/\____/_/
                 */
                $this->executeCleanup($backup, $target, $collector);

            } catch (\Exception $e) {
                $this->result->debug('exception: ' . $e->getMessage());
                $this->result->addError($e);
                $this->result->backupFailed($backup);
                if ($backup->stopOnFailure()) {
                    $stop = true;
                }
            }
        }
        $this->result->phpbuEnd();

        return $this->result;
    }

    /**
     * Handle configured ini settings.
     *
     * @param Configuration $configuration
     */
    public function handleIniSettings(Configuration $configuration)
    {
        // handle php.ini settings
        foreach ($configuration->getIniSettings() as $name => $value) {
            if (defined($value)) {
                $value = constant($value);
            }
            ini_set($name, $value);
        }
    }


    /**
     * Handles the php include_path settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return void
     */
    protected function handleIncludePath(Configuration $configuration)
    {
        $path = $configuration->getIncludePaths();
        if (count($path)) {
            $path = implode(PATH_SEPARATOR, $path);
            ini_set('include_path', $path . PATH_SEPARATOR . ini_get('include_path'));
        }
    }

    /**
     * Handles the bootstrap file inclusion.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    protected function handleBootstrap(Configuration $configuration)
    {
        $filename = $configuration->getBootstrap();

        if (!empty($filename)) {
            $pathToFile = stream_resolve_include_path($filename);
            if (!$pathToFile || !is_readable($pathToFile)) {
                throw new Exception(sprintf('Cannot open bootstrap file "%s".' . PHP_EOL, $filename));
            }
            require $pathToFile;
        }
    }

    /**
     * Creates the output printer.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result\PrinterCli
     */
    protected function createPrinter(Configuration $configuration)
    {
        $printer = new Result\PrinterCli(
            null,
            $configuration->getVerbose(),
            $configuration->getColors(),
            $configuration->getDebug()
        );

        return $printer;
    }

    /**
     * Create and register all configured loggers.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    protected function setupLoggers(Configuration $configuration)
    {
        /** @var \phpbu\App\Configuration\Logger $conf */
        foreach ($configuration->getLoggers() as $conf) {
            /** @var \phpbu\App\Listener $logger */
            $logger = Factory::createLogger($conf->type, $conf->options);
            $this->result->addListener($logger);
        }
    }

    /**
     * Create a target.
     *
     * @param  \phpbu\App\Configuration\Backup\Target $conf
     * @return \phpbu\App\Backup\Target
     * @throws \phpbu\App\Exception
     */
    protected function createTarget(Configuration\Backup\Target $conf)
    {
        $target = new Target($conf->dirname, $conf->filename);
        $target->setupPath();
        // add possible compressor
        if (!empty($conf->compression)) {
            $compressor = Compressor::create($conf->compression);
            $target->setCompressor($compressor);
        }
        return $target;
    }

    /**
     * Execute the backup.
     *
     * @param  \phpbu\App\Configuration\Backup $conf
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \Exception
     */
    protected function executeBackup(Configuration\Backup $conf, Target $target)
    {
        $this->result->backupStart($conf);
        $source = Factory::createSource($conf->getSource()->type, $conf->getSource()->options);
        $status = $source->backup($target, $this->result);
        if (is_a($status, '\\phpbu\\App\\Backup\\Source\\Status') && !$status->handledCompression()) {
            $this->handleCompression($target, $status);
        }
        $this->result->backupEnd($conf);
    }

    /**
     * Handle directory compression for sources which can't handle compression by them self.
     *
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Backup\Source\Status $status
     * @throws \phpbu\App\Exception
     */
    protected function handleCompression(Target $target, Status $status)
    {
        $dirCompressor = new Compressor\Directory($status->getDataPath());
        $dirCompressor->compress($target, $this->result);
    }

    /**
     * Execute checks.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Backup\Collector     $collector
     * @throws \Exception
     */
    protected function executeChecks(Configuration\Backup $backup, Target $target, Collector $collector)
    {
        /** @var \phpbu\App\Configuration\Backup\Check $check */
        foreach ($backup->getChecks() as $check) {
            try {
                $this->result->checkStart($check);
                $c = Factory::createCheck($check->type);
                if ($c->pass($target, $check->value, $collector, $this->result)) {
                    $this->result->checkEnd($check);
                } else {
                    $this->failure = true;
                    $this->result->checkFailed($check);
                }
            } catch (Backup\Check\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->checkFailed($check);
            }
        }
    }

    /**
     * Execute encryption.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param \phpbu\App\Backup\Target        $target
     */
    protected function executeCrypt(Configuration\Backup $backup, Target $target)
    {
        $crypt = $backup->getCrypt();
        if (!empty($crypt)) {
            try {
                $this->result->cryptStart($crypt);
                if ($this->failure && $crypt->skipOnFailure) {
                    $this->result->cryptSkipped($crypt);
                } else {
                    $c = Factory::createCrypter($crypt->type, $crypt->options);
                    $c->crypt($target, $this->result);
                    $target->setCrypter($c);
                }
            } catch (Backup\Crypter\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cryptFailed($crypt);
            }
        }
    }

    /**
     * Execute the syncs.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \Exception
     */
    protected function executeSyncs(Configuration\Backup $backup, Target $target)
    {
        /** @var \phpbu\App\Configuration\Backup\Sync $sync */
        foreach ($backup->getSyncs() as $sync) {
            try {
                $this->result->syncStart($sync);
                if ($this->failure && $sync->skipOnFailure) {
                    $this->result->syncSkipped($sync);
                } else {
                    $s = Factory::createSync($sync->type, $sync->options);
                    $s->sync($target, $this->result);
                    $this->result->syncEnd($sync);
                }
            } catch (Backup\Sync\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->syncFailed($sync);
            }
        }
    }

    /**
     * Execute the cleanup.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Backup\Collector     $collector
     * @throws \Exception
     */
    protected function executeCleanup(Configuration\Backup $backup, Target $target, Collector $collector)
    {
        $cleanup = $backup->getCleanup();
        if (!empty($cleanup)) {
            try {
                $this->result->cleanupStart($cleanup);
                if ($this->failure && $cleanup->skipOnFailure) {
                    $this->result->cleanupSkipped($cleanup);
                } else {
                    $cleaner = Factory::createCleaner($cleanup->type, $cleanup->options);
                    $cleaner->cleanup($target, $collector, $this->result);
                    $this->result->cleanupEnd($cleanup);
                }
            } catch (Backup\Cleaner\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cleanupFailed($cleanup);
            }
        }
    }
}
