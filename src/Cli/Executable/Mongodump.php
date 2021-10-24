<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Mongodump executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Mongodump extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Dump Directory
     *
     * @var string
     */
    private $dumpDir;

    /**
     * Use IPv6
     * --ipv6
     *
     * @var boolean
     */
    private $useIPv6;

    /**
     * Uri to connect to
     * --uri <uri>
     *
     * @var string
     */
    private $uri;

    /**
     * Host to connect to
     * --host <hostname:port>
     *
     * @var string
     */
    private $host;

    /**
     * User to connect with
     * --username <username>
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
     * Database to use for authentication
     * --authenticationDatabase <dbname>
     *
     * @var string
     */
    private $authenticationDatabase;

    /**
     * List of databases to backup
     * --db <database>
     *
     * @var array
     */
    private $databases = [];

    /**
     * List of collections to backup
     * --collection <collection>
     *
     * @var array
     */
    private $collections = [];

    /**
     * List of collections to ignore
     * --excludeCollections array of strings
     *
     * @var array
     */
    private $excludeCollections = [];

    /**
     * List of prefixes to exclude collections
     * --excludeCollectionWithPrefix array of strings
     *
     * @var array
     */
    private $excludeCollectionsWithPrefix = [];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('mongodump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set path to dump to.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function dumpToDirectory(string $path) : Mongodump
    {
        $this->dumpDir = $path;
        return $this;
    }

    /**
     * Use ipv6 to connect.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function useIpv6(bool $bool) : Mongodump
    {
        $this->useIPv6 = $bool;
        return $this;
    }

    /**
     * Set uri to dump from
     *
     * @param string $uri
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function useUri(string $uri) : Mongodump
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Set host to dump from.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function useHost(string $host) : Mongodump
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set credentials.
     *
     * @param  string $user
     * @param  string $password
     * @param  string $authDatabase
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function credentials(string $user = '', string $password = '', string $authDatabase = '') : Mongodump
    {
        $this->user                   = $user;
        $this->password               = $password;
        $this->authenticationDatabase = $authDatabase;
        return $this;
    }

    /**
     * Dump only given databases.
     *
     * @param  array $databases
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function dumpDatabases(array $databases) : Mongodump
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * Dump only given collections.
     *
     * @param  array $collections
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function dumpCollections(array $collections) : Mongodump
    {
        $this->collections = $collections;
        return $this;
    }

    /**
     * Exclude collections.
     *
     * @param  array $collections
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function excludeCollections(array $collections) : Mongodump
    {
        $this->excludeCollections = $collections;
        return $this;
    }

    /**
     * Exclude collections with given prefixes.
     *
     * @param  array $prefixes
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function excludeCollectionsWithPrefix(array $prefixes) : Mongodump
    {
        $this->excludeCollectionsWithPrefix = $prefixes;
        return $this;
    }

    /**
     * Mongodump CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        if (empty($this->dumpDir)) {
            throw new Exception('no directory to dump to');
        }
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOption('--out', $this->dumpDir, ' ');
        $cmd->addOptionIfNotEmpty('--ipv6', $this->useIPv6, false);
        $cmd->addOptionIfNotEmpty('--uri', $this->uri, true, ' ');
        $cmd->addOptionIfNotEmpty('--host', $this->host, true, ' ');
        $cmd->addOptionIfNotEmpty('--username', $this->user, true, ' ');
        $cmd->addOptionIfNotEmpty('--password', $this->password, true, ' ');
        $cmd->addOptionIfNotEmpty('--authenticationDatabase', $this->authenticationDatabase, true, ' ');

        if (count($this->databases)) {
            foreach ($this->databases as $db) {
                $cmd->addOption('--db', $db, ' ');
            }
        }

        if (count($this->collections)) {
            foreach ($this->collections as $col) {
                $cmd->addOption('--collection', $col, ' ');
            }
        }

        $cmd->addOptionIfNotEmpty('--excludeCollection', $this->excludeCollections);
        $cmd->addOptionIfNotEmpty('--excludeCollectionWithPrefix', $this->excludeCollectionsWithPrefix);

        return $process;
    }
}
