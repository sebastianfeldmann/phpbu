<?php


namespace phpbu\App\Backup\Restore;


/**
 * Class Plan
 *
 * @package    phpbu
 * @subpackage
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class Plan
{
    /**
     * List of commands to execute to restore the backup
     *
     * @var array
     */
    private $commands = [
        'decrypt'    => [],
        'extract'    => [],
        'restore'    => []
    ];

    /**
     * Add a decryption command to the restore plan
     *
     * @param  string $command
     * @return void
     */
    public function addDecryptionCommand(string $command): void
    {
        $this->commands['decrypt'][] = $command;
    }

    /**
     * Return the list of decryption commands
     *
     * @return array
     */
    public function getDecryptionCommands(): array
    {
        return $this->commands['decrypt'];
    }

    /**
     * Add an extraction command to the restore plan
     *
     * @param  string $command
     * @return void
     */
    public function addExtractCommand(string $command): void
    {
        $this->commands['extract'][] = $command;
    }

    /**
     * Return the list of extraction commands
     *
     * @return array
     */
    public function getExtractCommands(): array
    {
        return $this->commands['extract'];
    }

    /**
     * Add restore command to the restore plan
     *
     * @param  string $command
     * @return void
     */
    public function addRestoreCommand(string $command): void
    {
        $this->commands['restore'][] = $command;
    }

    /**
     * Return the list of restore commands
     *
     * @return array
     */
    public function getRestoreCommands(): array
    {
        return $this->commands['restore'];
    }
}
