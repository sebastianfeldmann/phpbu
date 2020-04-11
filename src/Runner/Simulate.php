<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Source;
use phpbu\App\Backup\Check;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;

/**
 * Simulate Runner
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 * @internal
 */
class Simulate extends Compression
{
    /**
     * Execute backups.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     * @throws \Exception
     */
    public function run(Configuration $configuration) : Result
    {
        $this->configuration = $configuration;
        $this->result->phpbuStart($configuration);

        // create backups
        /** @var \phpbu\App\Configuration\Backup $backup */
        foreach ($configuration->getBackups() as $backup) {
            // make sure the backup should be executed and is not excluded via the --limit option
            if (!$configuration->isBackupActive($backup->getName())) {
                $this->result->debug('skipping backup: ' . $backup->getName() . PHP_EOL);
                continue;
            }
            // setup target and collector
            $target    = $this->factory->createTarget($backup->getTarget());
            $target->setSize(20000000);
            $collector = new Local($target);
            $collector->setSimulation(true);

            $this->simulateSource($backup, $target);
            $this->simulateChecks($backup, $target, $collector);
            $this->simulateCrypt($backup, $target);
            $this->simulateSyncs($backup, $target);
            $this->simulateCleanup($backup, $target, $collector);
        }
        $this->result->phpbuEnd();

        return $this->result;
    }

    /**
     * Simulate the backup.
     *
     * @param  \phpbu\App\Configuration\Backup $conf
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \Exception
     */
    protected function simulateSource(Configuration\Backup $conf, Target $target)
    {
        /* @var \phpbu\App\Backup\Source $runner */
        $source = $this->factory->createSource($conf->getSource()->type, $conf->getSource()->options);

        $this->result->backupStart($conf, $target, $source);

        if ($source instanceof Source\Simulator) {
            $status = $source->simulate($target, $this->result);
            $this->compress($status, $target, $this->result);
        }
        $this->result->backupEnd($conf, $target, $source);
    }

    /**
     * Simulate checks.
     *
     * @param  \phpbu\App\Configuration\Backup   $backup
     * @param  \phpbu\App\Backup\Target          $target
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @throws \Exception
     */
    protected function simulateChecks(Configuration\Backup $backup, Target $target, Local $collector)
    {
        foreach ($backup->getChecks() as $config) {
            $this->result->checkStart($config);
            $check = $this->factory->createCheck($config->type);
            if ($check instanceof Check\Simulator) {
                $check->simulate($target, $config->value, $collector, $this->result);
            }
            $this->result->checkEnd($config);
        }
    }

    /**
     * Simulate encryption.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \phpbu\App\Exception
     */
    protected function simulateCrypt(Configuration\Backup $backup, Target $target)
    {
        if ($backup->hasCrypt()) {
            $crypt = $backup->getCrypt();
            $this->result->cryptStart($crypt);
            $crypter = $this->factory->createCrypter($crypt->type, $crypt->options);
            if ($crypter instanceof Crypter\Simulator) {
                $crypter->simulate($target, $this->result);
            }
            $target->setCrypter($crypter);
            $this->result->cryptEnd($crypt);
        }
    }

    /**
     * Simulate all syncs.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Target        $target
     * @throws \Exception
     */
    protected function simulateSyncs(Configuration\Backup $backup, Target $target)
    {
        /* @var \phpbu\App\Configuration\Backup\Sync $sync */
        foreach ($backup->getSyncs() as $sync) {
            $sync = $this->factory->createSync($sync->type, $sync->options);
            if ($sync instanceof Sync\Simulator) {
                $sync->simulate($target, $this->result);
            }
        }
    }

    /**
     * Simulate the cleanup.
     *
     * @param  \phpbu\App\Configuration\Backup   $backup
     * @param  \phpbu\App\Backup\Target          $target
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @throws \phpbu\App\Exception
     */
    protected function simulateCleanup(Configuration\Backup $backup, Target $target, Local $collector)
    {
        /* @var \phpbu\App\Configuration\Backup\Cleanup $cleanup */
        if ($backup->hasCleanup()) {
            $cleanup = $backup->getCleanup();
            $cleaner = $this->factory->createCleaner($cleanup->type, $cleanup->options);
            $this->result->cleanupStart($cleanup);
            if ($cleaner instanceof Cleaner\Simulator) {
                $cleaner->simulate($target, $collector, $this->result);
            }
            $this->result->cleanupEnd($cleanup);
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
        $result->debug($compressor->getExecutable($target)->getCommand());
        return $compressor->getArchiveFile($target);
    }
}
