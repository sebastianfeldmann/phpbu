<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\Command\Executable as Cmd;
use SebastianFeldmann\Cli\CommandLine;

/**
 * Mysqlimport Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0-dev
 */
class Mysqlimport extends Abstraction implements Executable
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
     * Target database
     *
     * @var string
     */
    private $database;

    /**
     * Name of the source file.
     *
     * @var string
     */
    private $sourceFilename;

    /**
     * Use mysqlimport with compression
     * -C
     *
     * @var bool
     */
    private $compress = false;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('mysqlimport', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the source filename and the target database.
     *
     * @param string $sourceFilename
     * @param string $targetDatabase
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function setSourceAndTarget(string $sourceFilename, string $targetDatabase) : Mysqlimport
    {
        $this->sourceFilename = $sourceFilename;
        $this->database       = $targetDatabase;
        return $this;
    }

    /**
     * Set the credentials.
     *
     * @param string $user
     * @param string $password
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function credentials(string $user = '', string $password = '') : Mysqlimport
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the hostname.
     *
     * @param string $host
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function useHost(string $host) : Mysqlimport
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the port.
     *
     * @param int $port
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function usePort(int $port) : Mysqlimport
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set the connection protocol.
     *
     * @param string $protocol
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function useProtocol(string $protocol) : Mysqlimport
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Use '-C' compress mode.
     *
     * @param bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    public function useCompression(bool $bool) : Mysqlimport
    {
        $this->compress = $bool;
        return $this;
    }

    /**
     * Mysqlimport CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine(): CommandLine
    {
        if (empty($this->database)) {
            throw new Exception('mysqlimport needs a target database to import into');
        }

        if (empty($this->sourceFilename)) {
            throw new Exception('mysqlimport needs a source file to import');
        }

        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addArgument($this->database);
        $cmd->addArgument($this->sourceFilename);
        $cmd->addOptionIfNotEmpty('--user', $this->user);
        $cmd->addOptionIfNotEmpty('--password', $this->password);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--port', $this->port);
        $cmd->addOptionIfNotEmpty('--protocol', $this->protocol);
        $cmd->addOptionIfNotEmpty('-C', $this->compress, false);

        return $process;
    }
}
