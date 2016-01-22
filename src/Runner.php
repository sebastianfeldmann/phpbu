<?php
namespace phpbu\App;

use phpbu\App\Backup;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Target;

/**
 * Runner actually executes all backup jobs.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Runner
{
    /**
     * phpbu Factory
     *
     * @var \phpbu\App\Factory
     */
    protected $factory;

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
     * App Configuration
     *
     * @var \phpbu\App\Configuration
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param \phpbu\App\Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Run phpbu
     *
     * @param  \phpbu\App\Configuration $configuration
     * @param  \phpbu\App\Factory
     * @return \phpbu\App\Result
     */
    public function run(Configuration $configuration)
    {
        // TODO: don't rely on static/global settings this is ugly
        Util\Cli::registerBase('configuration', $configuration->getWorkingDirectory());

        $stop                = false;
        $this->result        = new Result();
        $this->configuration = $configuration;

        $this->setupEnvironment($configuration);
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
                $this->executeSource($backup, $target);

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
     * This executes a bootstrap runner to handle ini settings and the bootstrap file inclusion.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    protected function setupEnvironment(Configuration $configuration)
    {
        $runner = $this->factory->createRunner('Bootstrap', $this->configuration->isSimulation());
        $runner->run($configuration);
    }

    /**
     * Create and register all configured loggers.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    protected function setupLoggers(Configuration $configuration)
    {
        foreach ($configuration->getLoggers() as $log) {
            // this is a already fully setup Listener so just add it
            if ($log instanceof Listener) {
                $logger = $log;
            } else {
                // this is a configuration blueprint for a logger, so create and add it
                /** @var \phpbu\App\Configuration\Logger $log */
                /** @var \phpbu\App\Listener $logger */
                $logger = $this->factory->createLogger($log->type, $log->options);
            }
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
    protected function executeSource(Configuration\Backup $conf, Target $target)
    {
        $this->result->backupStart($conf);
        /* @var \phpbu\App\Runner\Source $runner */
        $source = $this->factory->createSource($conf->getSource()->type, $conf->getSource()->options);
        $runner = $this->factory->createRunner('source', $this->configuration->isSimulation());
        $runner->run($source, $target, $this->result);
        $this->result->backupEnd($conf);
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
        $runner = $this->factory->createRunner('check', $this->configuration->isSimulation());
        /** @var \phpbu\App\Configuration\Backup\Check $check */
        foreach ($backup->getChecks() as $config) {
            $check = $this->factory->createCheck($config->type);
            $runner->run($check, $config, $target, $collector, $this->result);
        }
        $this->failure = $runner->hasFailed();
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
                    $c = $this->factory->createCrypter($crypt->type, $crypt->options);
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
                    $s = $this->factory->createSync($sync->type, $sync->options);
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
                    $cleaner = $this->factory->createCleaner($cleanup->type, $cleanup->options);
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
