<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Mysql Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0-dev
 */
class Mysql extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Host to connect to
     * --host <hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * --port <number>
     *
     * @var int
     */
    private $port;

    /**
     * The connection protocol
     * --protocol
     *
     * @var string
     */
    private $protocol;

    /**
     * User to connect with
     * --user <username>
     *
     * @var string
     */
    private $user;

    /**
     * Password to authenticate with
     * --password <password>
     *
     * @var string
     */
    private $password;

    /**
     * Database to connect to
     * --database <database>
     *
     * @var string
     */
    private $database;

    /**
     * Use mysql quick mode
     * -q
     *
     * @var bool
     */
    private $quick = false;

    /**
     * Use mysql with compression
     * -C
     *
     * @var bool
     */
    private $compress = false;

    /**
     * Name of the source file.
     * -e "source $file"
     *
     * @var string
     */
    private $sourceFilename;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('mysql', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function credentials(string $user = '', string $password = '') : Mysql
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the hostname.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useHost(string $host) : Mysql
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the port.
     *
     * @param  int $port
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function usePort(int $port) : Mysql
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set the connection protocol.
     *
     * @param  string $protocol
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useProtocol(string $protocol) : Mysql
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Set the database.
     *
     * @param  string $database
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useDatabase(string $database) : Mysql
    {
        $this->database = $database;
        return $this;
    }

    /**
     * Use '-q' quick mode.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useQuickMode(bool $bool) : Mysql
    {
        $this->quick = $bool;
        return $this;
    }

    /**
     * Use '-C' compress mode.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useCompression(bool $bool) : Mysql
    {
        $this->compress = $bool;
        return $this;
    }

    /**
     * Set the source filename.
     *
     * @param string $sourceFilename
     *
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    public function useSourceFile(string $sourceFilename) : Mysql
    {
        $this->sourceFilename = $sourceFilename;
        return $this;
    }

    /**
     * Mysql CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('--user', $this->user);
        $cmd->addOptionIfNotEmpty('--password', $this->password);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--port', $this->port);
        $cmd->addOptionIfNotEmpty('--protocol', $this->protocol);
        $cmd->addOptionIfNotEmpty('--database', $this->database);
        $cmd->addOptionIfNotEmpty('-q', $this->quick, false);
        $cmd->addOptionIfNotEmpty('-C', $this->compress, false);

        if (!empty($this->sourceFilename)) {
            $cmd->addOption('--execute', 'source '.$this->sourceFilename);
        }

        return $process;
    }
}
