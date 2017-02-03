<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

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
    public function __construct(string $path = '')
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
    public function runCommand(string $command) : RedisCli
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
    public function backup() : RedisCli
    {
        return $this->runCommand('BGSAVE');
    }

    /**
     * Execute redis-cli LASTSAVE command.
     *
     * @return \phpbu\App\Cli\Executable\RedisCli
     * @throws \phpbu\App\Exception
     */
    public function lastBackupTime() : RedisCli
    {
        return $this->runCommand('LASTSAVE');
    }

    /**
     * Host to connect to.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\RedisCli
     */
    public function useHost(string $host) : RedisCli
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
    public function usePort(int $port) : RedisCli
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
    public function usePassword(string $password) : RedisCli
    {
        $this->password = $password;
        return $this;
    }

    /**
     * RedisCli CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        if (empty($this->command)) {
            throw new Exception('Choose command to execute');
        }

        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $this->setOptions($cmd);
        $cmd->addOption($this->command);

        return $process;
    }

    /**
     * Set the openssl command line options.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    protected function setOptions(Cmd $cmd)
    {
        $cmd->addOptionIfNotEmpty('-h', $this->host, true, ' ');
        $cmd->addOptionIfNotEmpty('-p', $this->port, true, ' ');
        $cmd->addOptionIfNotEmpty('-a', $this->password, true, ' ');
    }
}
