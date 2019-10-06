<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Decompressor;
use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;
use SebastianFeldmann\Cli\Util;

/**
 * Restore Runner
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0
 * @internal
 */
class Restore extends Process
{
    /**
     * Restore all backups
     *
     * @param  \phpbu\App\Configuration $configuration
     * @return \phpbu\App\Result
     * @throws \Exception
     */
    public function run(Configuration $configuration) : Result
    {
        foreach ($configuration->getBackups() as $backup) {
            if ($configuration->isBackupActive($backup->getName())) {
                $this->printPlan($this->createRestorePlan($backup));
            }
        }

        return $this->result;
    }

    /**
     * Collects all restore commands in a restore plan
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @return \phpbu\App\Backup\Restore\Plan
     * @throws \phpbu\App\Exception
     */
    private function createRestorePlan(Configuration\Backup $backup): Plan
    {
        $plan   = new Plan();
        $target = $this->factory->createTarget($backup->getTarget());
        $this->decryptBackup($target, $backup, $plan);
        $this->restoreBackup($target, $backup, $plan);

        return $plan;
    }

    /**
     * Output the restore plan
     *
     * @param \phpbu\App\Backup\Restore\Plan $plan
     */
    private function printPlan(Plan $plan)
    {
        $this->printDecryptionCommands($plan);
        $this->printRestoreCommands($plan);
    }

    /**
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Restore\Plan  $plan
     * @throws \phpbu\App\Exception
     */
    private function decryptBackup(Target $target, Configuration\Backup $backup, Plan $plan)
    {
        if (!$backup->hasCrypt()) {
            return;
        }
        $cryptConf = $backup->getCrypt();
        $crypt     = $this->factory->createCrypter($cryptConf->type, $cryptConf->options);

        // check if decryption is supported
        if (!$crypt instanceof Crypter\Restorable) {
            $plan->markCryptAsUnsupported();
            return;
        }
        $crypt->restore($target, $plan);
    }

    /**
     * @param  \phpbu\App\Backup\Target        $target
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  \phpbu\App\Backup\Restore\Plan  $plan
     * @throws \phpbu\App\Exception
     */
    private function restoreBackup(Target $target, Configuration\Backup $backup, Plan $plan)
    {
        $sourceConf = $backup->getSource();
        $source     = $this->factory->createSource($sourceConf->type, $sourceConf->options);
        // make sure restore is supported
        if (!$source instanceof Source\Restorable) {
            $plan->markSourceAsUnsupported();
            return;
        }
        // pass plan to source to collect restore commands
        $status = $source->restore($target, $plan);

        // make sure we decompress the backup
        if ($target->shouldBeCompressed()) {
            $decompressor = new Decompressor\File();
            $command      = $decompressor->decompress($target);
            $plan->addDecompressionCommand($command);
            $target->disableCompression();
        }

        // if source created a directory we have to un-tar the decompressed backup
        if ($status->isDirectory()) {
            $decompressor = new Decompressor\Directory();
            $command      = $decompressor->decompress($target);

            $target->removeFileSuffix('tar');
            $plan->addDecompressionCommand($command);
        }
    }

    /**
     * Output the decryption commands
     *
     * @param \phpbu\App\Backup\Restore\Plan $plan
     * @return void
     */
    private function printDecryptionCommands(Plan $plan): void
    {
        if (!$plan->isCryptSupported()) {
            echo Util::formatWithColor('fg-red', "WARNING: Your configured crypt does not support restore for now.\n");
            return;
        }
        $commands = $plan->getDecryptionCommands();

        if (empty($commands)) {
            return;
        }

        echo Util::formatWithColor('fg-yellow', "# Decrypt your backup\n");
        $this->printCommands($commands);
    }

    /**
     * Output the restore commands
     *
     * @param \phpbu\App\Backup\Restore\Plan $plan
     * @return void
     */
    private function printRestoreCommands(Plan $plan): void
    {
        if (!$plan->isSourceSupported()) {
            echo Util::formatWithColor('fg-red', "WARNING: Your configured source does not support restore for now.\n");
            return;
        }

        $this->printExtractionCommands($plan->getDecompressionCommands());

        echo Util::formatWithColor('fg-yellow', "# Restore your data [BE CAREFUL]\n");
        $this->printCommands($plan->getRestoreCommands());
    }

    /**
     * Output extraction commands
     *
     * @param  array $commands
     * @return void
     */
    private function printExtractionCommands(array $commands): void
    {
        if (!empty($commands)) {
            echo Util::formatWithColor('fg-yellow', "# Extract your backup \n");
            $this->printCommands($commands);
        }
    }

    /**
     * Print restore plan commands
     *
     * @param  array $commands
     * @return void
     */
    private function printCommands(array $commands): void
    {
        foreach ($commands as $cmd) {
            if (!empty($cmd['comment'])) {
                echo Util::formatWithColor('fg-yellow', '# ' . $cmd['comment'] . PHP_EOL);
            }
            echo $cmd['command'] . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
