<?php
namespace phpbu\App\Runner;

use phpbu\App\Exception;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Factory;
use phpbu\App\Result;

/**
 * Backup Runner
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Backup
{
    /**
     * @var \phpbu\App\Factory
     */
    private $factory;

    /**
     * Backup failed
     *
     * @var bool
     */
    private $failure;

    /**
     * phpbu Result
     *
     * @var \phpbu\App\Result
     */
    private $result;

    /**
     * phpbu Configuration
     *
     * @var \phpbu\App\Configuration
     */
    private $configuration;

    /**
     * Backup constructor.
     *
     * @param \phpbu\App\Factory $factory
     * @param \phpbu\App\Result  $result
     */
    public function __construct(Factory $factory, Result $result)
    {
        $this->factory = $factory;
        $this->result  = $result;
    }

    /**
     * Execute backups.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function run(Configuration $configuration) : Result
    {
        $this->configuration = $configuration;
        $stop                = false;

        $this->result->phpbuStart($configuration);

        // create backups
        /** @var \phpbu\App\Configuration\Backup $backup */
        foreach ($configuration->getBackups() as $backup) {
            if ($stop) {
                break;
            }
            // make sure the backup should be executed and is not excluded via the --limit option
            if (!$configuration->isBackupActive($backup->getName())) {
                $this->result->debug('skipping backup: ' . $backup->getName() . PHP_EOL);
                continue;
            }
            // setup target and collector, reset failure state
            $target        = $this->factory->createTarget($backup->getTarget());
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
        /* @var \phpbu\App\Runner\Check $runner */
        $runner = $this->factory->createRunner('check', $this->configuration->isSimulation());
        foreach ($backup->getChecks() as $config) {
            try {
                $this->result->checkStart($config);
                $check = $this->factory->createCheck($config->type);
                if ($runner->run($check, $target, $config->value, $collector, $this->result)) {
                    $this->result->checkEnd($config);
                } else {
                    $this->failure = true;
                    $this->result->checkFailed($config);
                }
            } catch (Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->checkFailed($config);
            }
        }
    }

    /**
     * Execute encryption.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \phpbu\App\Exception
     */
    protected function executeCrypt(Configuration\Backup $backup, Target $target)
    {
        if ($backup->hasCrypt()) {
            $crypt = $backup->getCrypt();
            try {
                $this->result->cryptStart($crypt);
                if ($this->failure && $crypt->skipOnFailure) {
                    $this->result->cryptSkipped($crypt);
                } else {
                    /* @var \phpbu\App\Runner\Crypter $runner */
                    $runner  = $this->factory->createRunner('crypter', $this->configuration->isSimulation());
                    $runner->run($this->factory->createCrypter($crypt->type, $crypt->options), $target, $this->result);
                }
            } catch (Crypter\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cryptFailed($crypt);
            }
        }
    }

    /**
     * Execute all syncs.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \Exception
     */
    protected function executeSyncs(Configuration\Backup $backup, Target $target)
    {
        /* @var \phpbu\App\Runner\Crypter $runner */
        /* @var \phpbu\App\Configuration\Backup\Sync $sync */
        $runner  = $this->factory->createRunner('sync', $this->configuration->isSimulation());
        foreach ($backup->getSyncs() as $sync) {
            try {
                $this->result->syncStart($sync);
                if ($this->failure && $sync->skipOnFailure) {
                    $this->result->syncSkipped($sync);
                } else {
                    $runner->run($this->factory->createSync($sync->type, $sync->options), $target, $this->result);
                    $this->result->syncEnd($sync);
                }
            } catch (Sync\Exception $e) {
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
     * @throws \phpbu\App\Exception
     */
    protected function executeCleanup(Configuration\Backup $backup, Target $target, Collector $collector)
    {
        /* @var \phpbu\App\Runner\Cleaner $runner */
        /* @var \phpbu\App\Configuration\Backup\Cleanup $cleanup */
        if ($backup->hasCleanup()) {
            $cleanup = $backup->getCleanup();
            try {
                $runner = $this->factory->createRunner('cleaner', $this->configuration->isSimulation());
                $this->result->cleanupStart($cleanup);
                if ($this->failure && $cleanup->skipOnFailure) {
                    $this->result->cleanupSkipped($cleanup);
                } else {
                    $cleaner = $this->factory->createCleaner($cleanup->type, $cleanup->options);
                    $runner->run($cleaner, $target, $collector, $this->result);
                    $this->result->cleanupEnd($cleanup);
                }
            } catch (Cleaner\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cleanupFailed($cleanup);
            }
        }
    }
}
