<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

/**
 * Mcrypt executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Mcrypt extends Abstraction implements Executable
{
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
    public function __construct($path = null)
    {
        $this->cmd = 'mcrypt';
        parent::__construct($path);
    }

    /**
     * Set the target file.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function saveAt($path)
    {
        $this->targetFile = $path;
        return $this;
    }

    /**
     * Delete the uncrypted data.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mcrypt
     */
    public function deleteUncrypted($bool)
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
    public function useKey($key)
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
    public function useKeyFile($keyFile)
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
    public function useAlgorithm($algorithm)
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
    public function useHash($hash)
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
    public function useConfig($path)
    {
        $this->config = $path;
        return $this;
    }

    /**
     * Subclass Process generator.
     *
     * @return \phpbu\App\Cli\Process
     * @throws \phpbu\App\Exception
     */
    protected function createProcess()
    {
        if(empty($this->targetFile)) {
            throw new Exception('target file is missing');
        }
        if(empty($this->key) && empty($this->keyFile)) {
            throw new Exception('one of \'key\' or \'keyFile\' is mandatory');
        }
        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }

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
