<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Gpg executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.1
 */
class Gpg extends Abstraction implements Executable
{
    use OptionMasker;

    public const SUFFIX = 'gpg';

    private const ACTION_ENCRYPT = 'e';
    private const ACTION_DECRYPT = 'd';

    /**
     * Current action encrypt|decrypt
     *
     * @var string
     */
    private $action;

    /**
     * Path to source file
     *
     * @var string
     */
    private $sourceFile;

    /**
     * Path to encrypted file
     *
     * @var string
     */
    private $targetFile;

    /**
     * Gpg user
     *
     * @var string
     */
    private $user;

    /**
     * Keep the not encrypted file
     *
     * @var bool
     */
    private $deleteSource = true;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('gpg', $path);
    }

    /**
     * Encrypt a file
     *
     * @param  string $file
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    public function encryptFile(string $file): Gpg
    {
        $this->action     = self::ACTION_ENCRYPT;
        $this->sourceFile = $file;
        $this->targetFile = $file . '.gpg';
        return $this;
    }

    /**
     * Encrypt a file
     *
     * @param  string $file
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    public function decryptFile(string $file): Gpg
    {
        $this->action     = self::ACTION_DECRYPT;
        $this->sourceFile = $file . '.gpg';
        $this->targetFile = $file;
        return $this;
    }

    /**
     * Delete the uncrypted data
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    public function deleteSource(bool $bool): Gpg
    {
        $this->deleteSource = $bool;
        return $this;
    }

    /**
     * Password to use for encryption
     *
     * @param  string $user
     * @return \phpbu\App\Cli\Executable\Gpg
     */
    public function useUser(string $user): Gpg
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Gpg CommandLine generator
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine(): CommandLine
    {
        if (empty($this->sourceFile)) {
            throw new Exception('file is missing');
        }

        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);

        $process->addCommand($cmd);

        $this->addDeleteCommand($process);

        return $process;
    }

    /**
     * Add the 'rm' command to remove the uncrypted file
     *
     * @param \SebastianFeldmann\Cli\CommandLine $process
     */
    protected function addDeleteCommand(CommandLine $process): void
    {
        if ($this->deleteSource) {
            $cmd = new Cmd('rm');
            $cmd->addArgument($this->sourceFile);
            $process->addCommand($cmd);
        }
    }
}
