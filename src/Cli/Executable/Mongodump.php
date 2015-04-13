<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

/**
 * Mongodump executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Mongodump extends Abstraction implements Executable
{
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
     * Host to connect to
     * --host <hostname:port>
     *
     * @var string
     */
    private $host;

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
    private $databases;

    /**
     * List of collections to backup
     * --collection <collection>
     *
     * @var array
     */
    private $collections;

    /**
     * List of collections to ignore
     * --excludeCollections array of strings
     *
     * @var array
     */
    private $excludeCollections;

    /**
     * List of prefixes to exclude collections
     * --excludeCollectionWithPrefix array of strings
     *
     * @var array
     */
    private $excludeCollectionsWithPrefix;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->cmd = 'mongodump';
        parent::__construct($path);
    }

    /**
     * Set path to dump to.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function dumpToDirectory($path)
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
    public function useIpv6($bool)
    {
        $this->useIPv6 = $bool;
        return $this;
    }

    /**
     * Set host to dump from.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Mongodump
     */
    public function useHost($host)
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
    public function credentials($user = null, $password = null, $authDatabase = null)
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
    public function dumpDatabases(array $databases)
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
    public function dumpCollections(array $collections)
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
    public function excludeCollections(array $collections)
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
    public function excludeCollectionsWithPrefix(array $prefixes)
    {
        $this->excludeCollectionsWithPrefix = $prefixes;
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
        if (empty($this->dumpDir)) {
            throw new Exception('no directory to dump to');
        }
        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }

        $cmd->addOption('--out', $this->dumpDir, ' ');
        $cmd->addOptionIfNotEmpty('--ipv6', $this->useIPv6, false);
        $cmd->addOptionIfNotEmpty('--host', $this->host, true, ' ');
        $cmd->addOptionIfNotEmpty('--user', $this->user, true, ' ');
        $cmd->addOptionIfNotEmpty('--password', $this->password, true, ' ');
        $cmd->addOptionIfNotEmpty('--authenticationDatabase', $this->authenticationDatabase, true, ' ');

        if (count($this->databases)) {
            foreach ($this->databases as $db) {
                $cmd->addOption('--database', $db, ' ');
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
