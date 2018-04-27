<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Arangodump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Arangodump extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Endpoint to connect to
     * --server.endpoint <endpoint>
     *
     * @var string
     */
    private $endpoint;

    /**
     * Username to connect with
     * --server.username <username>
     *
     * @var string
     */
    private $username;

    /**
     * Password to authenticate with
     * --server.password <password>
     *
     * @var string
     */
    private $password;

    /**
     * The database to backup
     * --server.database <database>
     *
     * @var string
     */
    private $database;

    /**
     * Whether the data should be dumped or not
     * --dump-data
     *
     * @var boolean
     */
    private $dumpData;

    /**
     * Include system collections
     * --include-system-collections
     *
     * @var boolean
     */
    private $includeSystemCollections;

    /**
     * Restrict the dump to these collections
     * --collection
     *
     * @var array
     */
    private $collections = [];

    /**
     * Do not ask for the username and password when connecting to the server.
     * This does not control whether the server requires authentication.
     * -- disable-authentication
     *
     * @var boolean
     */
    private $disableAuthentication;

    /**
     * Directory to dump to.
     *
     * @var string
     */
    private $dumpDir;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('arangodump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set target dump directory.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function dumpTo(string $path) : Arangodump
    {
        $this->dumpDir = $path;
        return $this;
    }

    /**
     * Set user credentials.
     *
     * @param  string $username
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function credentials(string $username = '', string $password = '') : Arangodump
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * Endpoint to use.
     *
     * @param  string $endpoint
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function useEndpoint(string $endpoint) : Arangodump
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Database to dump.
     *
     * @param  string $database
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function dumpDatabase(string $database) : Arangodump
    {
        $this->database = $database;
        return $this;
    }

    /**
     * Collections to dump.
     *
     * @param  array $collections
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function dumpCollections(array $collections) : Arangodump
    {
        $this->collections = $collections;
        return $this;
    }

    /**
     * Disable authentication.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function disableAuthentication(bool $bool) : Arangodump
    {
        $this->disableAuthentication = $bool;
        return $this;
    }

    /**
     * Dump system collections.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function includeSystemCollections(bool $bool) : Arangodump
    {
        $this->includeSystemCollections = $bool;
        return $this;
    }

    /**
     * Dump data as well.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Arangodump
     */
    public function dumpData(bool $bool) : Arangodump
    {
        $this->dumpData = $bool;
        return $this;
    }

    /**
     * Arangodump CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    public function createCommandLine() : CommandLine
    {
        if (empty($this->dumpDir)) {
            throw new Exception('dump dir is mandatory');
        }

        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('--server.username', $this->username, true, ' ');
        $cmd->addOptionIfNotEmpty('--server.password', $this->password, true, ' ');
        $cmd->addOptionIfNotEmpty('--server.endpoint', $this->endpoint, true, ' ');
        $cmd->addOptionIfNotEmpty('--server.database', $this->database, true, ' ');

        $this->handleCollections($cmd);

        if ($this->disableAuthentication) {
            $cmd->addOption('--server.disable-authentication', 'true', ' ');
        }

        $this->handleDump($cmd);

        return $process;
    }

    /**
     * Handle command collection settings.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    protected function handleCollections(Cmd $cmd)
    {
        if (count($this->collections)) {
            foreach ($this->collections as $collection) {
                $cmd->addOption('--collection', $collection, ' ');
            }
        }
        if ($this->includeSystemCollections) {
            $cmd->addOption('--include-system-collections', 'true', ' ');
        }
    }

    /**
     * Handle command data settings.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    protected function handleDump(Cmd $cmd)
    {
        if ($this->dumpData) {
            $cmd->addOption('--dump-data', 'true', ' ');
        }
        $cmd->addOption('--output-directory', $this->dumpDir, ' ');
    }
}
