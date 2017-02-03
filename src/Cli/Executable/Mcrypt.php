<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Mcrypt executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Mcrypt extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Key to pass via cli
     *
     * @var string
     */
    private $key;

    /**
     * Key file
     *
     * @var string
     */
    private $keyFile;

    /**
     * Algorithm to use
     *
     * @var string
     */
    private $algorithm;

    /**
     * Hash to use
     *
     * @var string
     */
    private $hash;

    /**
     * Path to config file
     *
     * @var string
     */
    private $config;

    /**
     * Keep the not encrypted file
     *
     * @var boolean
     */
    private $deleteUncrypted = true;

    /**
     * Path to the encrypted file
     *
     * @var string
     */
    private $targetFile;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('mcrypt', $path);
        $this->setMaskCandidates(['key']);
    }

    /**
     * Set the target file.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function saveAt(string $path) : Mcrypt
    {
        $this->targetFile = $path;
        return $this;
    }

    /**
     * Delete the uncrypted data.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function deleteUncrypted(bool $bool) : Mcrypt
    {
        $this->deleteUncrypted = $bool;
        return $this;
    }

    /**
     * Key to use for encryption.
     *
     * @param  string $key
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function useKey(string $key) : Mcrypt
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Key file to use for encryption.
     *
     * @param  string $keyFile
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function useKeyFile(string $keyFile) : Mcrypt
    {
        $this->keyFile = $keyFile;
        return $this;
    }

    /**
     * Set algorithm to use.
     *
     * @param  string $algorithm
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function useAlgorithm(string $algorithm) : Mcrypt
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * Hash to use for encryption.
     *
     * @param  string $hash
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function useHash(string $hash) : Mcrypt
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Set path to sync to.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function useConfig(string $path) : Mcrypt
    {
        $this->config = $path;
        return $this;
    }

    /**
     * Mcrypt CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        if (empty($this->targetFile)) {
            throw new Exception('target file is missing');
        }
        if (empty($this->key) && empty($this->keyFile)) {
            throw new Exception('one of \'key\' or \'keyFile\' is mandatory');
        }
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('-u', $this->deleteUncrypted, false);
        $cmd->addOptionIfNotEmpty('-k', $this->key, true, ' ');
        $cmd->addOptionIfNotEmpty('-f', $this->keyFile, true, ' ');
        $cmd->addOptionIfNotEmpty('-h', $this->hash, true, ' ');
        $cmd->addOptionIfNotEmpty('-a', $this->algorithm, true, ' ');
        $cmd->addOptionIfNotEmpty('-c', $this->config, true, ' ');

        $cmd->addArgument($this->targetFile);

        return $process;
    }
}
