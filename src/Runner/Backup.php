<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Compressor;
use phpbu\App\Exception;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
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
 * @internal
 */
class Backup extends Compression
{
    /**
     * Backup failed
     *
     * @var bool
     */
    protected $failure;

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
            $collector     = new Local($target);
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
        $source = $this->factory->createSource($conf->getSource()->type, $conf->getSource()->options);
        $status = $source->backup($target, $this->result);
        $this->compress($status, $target, $this->result);
        $this->result->backupEnd($conf);
    }

    /**
     * Execute checks.
     *
     * @param  \phpbu\App\Configuration\Backup   $backup
     * @param  \phpbu\App\Backup\Target          $target
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @throws \Exception
     */
    protected function executeChecks(Configuration\Backup $backup, Target $target, Local $collector)
    {
        foreach ($backup->getChecks() as $config) {
            try {
                $this->result->checkStart($config);
                $check = $this->factory->createCheck($config->type);
                if ($check->pass($target, $config->value, $collector, $this->result)) {
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
            $config = $backup->getCrypt();
            try {
                $this->result->cryptStart($config);
                if ($this->failure && $config->skipOnFailure) {
                    $this->result->cryptSkipped($config);
                    return;
                }
                $crypter = $this->factory->createCrypter($config->type, $config->options);
                $crypter->crypt($target, $this->result);
                $target->setCrypter($crypter);
                $this->result->cryptEnd($config);
            } catch (Crypter\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cryptFailed($config);
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
        /* @var \phpbu\App\Configuration\Backup\Sync $sync */
        foreach ($backup->getSyncs() as $config) {
            try {
                $this->result->syncStart($config);
                if ($this->failure && $config->skipOnFailure) {
                    $this->result->syncSkipped($config);
                    return;
                }
                $sync = $this->factory->createSync($config->type, $config->options);
                $sync->sync($target, $this->result);
                $this->result->syncEnd($config);
            } catch (Sync\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->syncFailed($config);
            }
        }
    }

    /**
     * Execute the cleanup.
     *
     * @param  \phpbu\App\Configuration\Backup   $backup
     * @param  \phpbu\App\Backup\Target          $target
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @throws \phpbu\App\Exception
     */
    protected function executeCleanup(Configuration\Backup $backup, Target $target, Local $collector)
    {
        if ($backup->hasCleanup()) {
            /* @var \phpbu\App\Configuration\Backup\Cleanup $config */
            $config = $backup->getCleanup();
            try {
                $this->result->cleanupStart($config);
                if ($this->failure && $config->skipOnFailure) {
                    $this->result->cleanupSkipped($config);
                    return;
                }
                $cleaner = $this->factory->createCleaner($config->type, $config->options);
                $cleaner->cleanup($target, $collector, $this->result);
                $this->result->cleanupEnd($config);
            } catch (Cleaner\Exception $e) {
                $this->failure = true;
                $this->result->addError($e);
                $this->result->cleanupFailed($config);
            }
        }
    }

    /**
     * Execute the compressor.
     * Returns the path to the created archive file.
     *
     * @param  \phpbu\App\Backup\Compressor\Compressible $compressor
     * @param  \phpbu\App\Backup\Target                  $target
     * @param  \phpbu\App\Result                         $result
     * @return string
     */
    protected function executeCompressor(Compressor\Compressible $compressor, Target $target, Result $result) : string
    {
        return $compressor->compress($target, $result);
    }
}
