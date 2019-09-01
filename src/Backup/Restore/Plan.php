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
        'decompress'    => [],
        'restore'    => []
    ];

    /**
     * Is used crypt supported
     *
     * @var bool
     */
    private $supportedCrypt = true;

    /**
     * Is used source supported
     *
     * @var bool
     */
    private $supportedSource = true;

    /**
     * Mark the crypt implementation as not supported to restore
     *
     * @return void
     */
    public function markCryptAsUnsupported(): void
    {
        $this->supportedCrypt = false;
    }

    /**
     * Does crypt support restore
     *
     * @return bool
     */
    public function isCryptSupported(): bool
    {
        return $this->supportedCrypt;
    }

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
     * Add an decompression command to the restore plan
     *
     * @param  string $command
     * @return void
     */
    public function addDecompressionCommand(string $command): void
    {
        $this->commands['decompress'][] = $command;
    }

    /**
     * Return the list of decompression commands
     *
     * @return array
     */
    public function getDecompressionCommands(): array
    {
        return $this->commands['decompress'];
    }

    /**
     * Mark used source as unsupported to restore
     *
     * @return void
     */
    public function markSourceAsUnsupported(): void
    {
        $this->supportedSource = false;
    }

    /**
     * Does the source support restore
     *
     * @return bool
     */
    public function isSourceSupported(): bool
    {
        return $this->supportedSource;
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
