<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

/**
 * RedisCli executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class RedisCli extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * List of implemented redis commands
     *
     * @var array
     */
    private $availableCommands = ['BGSAVE' => true, 'LASTSAVE' => true];

    /**
     * Redis command to execute
     *
     * @var string
     */
    private $command;

    /**
     * Host to connect to
     * -h
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * -p
     *
     * @var integer
     */
    private $port;


    /**
     * Password to connect
     * -a
     *
     * @var string
     */
    private $password;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->setup('redis-cli', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the redis-cli command to execute.
     *
     * @param  string $command
     * @return \phpbu\App\Cli\Executable\RedisCli
     * @throws \phpbu\App\Exception
     */
    public function runCommand($command)
    {
        if(!isset($this->availableCommands[$command])) {
            throw new Exception('Unknown redis-cli command');
        }
        $this->command = $command;
        return $this;
    }

    /**
     * Execute redis-cli BGSAVE command.
     *
     * @return \phpbu\App\Cli\Executable\RedisCli
     * @throws \phpbu\App\Exception
     */
    public function backup()
    {
        return $this->runCommand('BGSAVE');
    }

    /**
     * Execute redis-cli LASTSAVE command.
     *
     * @return \phpbu\App\Cli\Executable\RedisCli
     * @throws \phpbu\App\Exception
     */
    public function lastBackupTime()
    {
        return $this->runCommand('LASTSAVE');
    }

    /**
     * Host to connect to.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\RedisCli
     */
    public function useHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Port to connect to.
     *
     * @param  int $port
     * @return \phpbu\App\Cli\Executable\RedisCli
     */
    public function usePort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Password to authenticate.
     *
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\RedisCli
     */
    public function usePassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * RedisCli Process generator.
     *
     * @return \phpbu\App\Cli\Process
     * @throws \phpbu\App\Exception
     */
    protected function createProcess()
    {
        if (empty($this->command)) {
            throw new Exception('Choose command to execute');
        }

        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $this->setOptions($cmd);
        $cmd->addOption($this->command);

        return $process;
    }

    /**
     * Set the openssl command line options
     *
     * @param \phpbu\App\Cli\Cmd $cmd
     */
    protected function setOptions(Cmd $cmd)
    {
        $cmd->addOptionIfNotEmpty('-h', $this->host, true, ' ');
        $cmd->addOptionIfNotEmpty('-p', $this->port, true, ' ');
        $cmd->addOptionIfNotEmpty('-a', $this->password, true, ' ');
    }
}
