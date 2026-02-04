<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Mongodump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class Mongodump extends SimulatorExecutable implements Simulator
{
    /**
     * Path to mongodump command.
     *
     * @var string
     */
    private $pathToMongodump;

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
     * (No PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupSourceData($conf);
        $this->setupCredentials($conf);

        $this->pathToMongodump = Util\Arr::getValue($conf, 'pathToMongodump', '');
        $this->useIPv6         = Util\Str::toBoolean(Util\Arr::getValue($conf, 'ipv6', ''), false);

        $this->setupValidation();
    }

    /**
     * Fetch databases and collections to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->databases                    = Util\Str::toList(Util\Arr::getValue($conf, 'databases', ''));
        $this->collections                  = Util\Str::toList(Util\Arr::getValue($conf, 'collections', ''));
        $this->excludeCollections           = Util\Str::toList(Util\Arr::getValue($conf, 'excludeCollections', ''));
        $this->excludeCollectionsWithPrefix = Util\Str::toList(
            Util\Arr::getValue($conf, 'excludeCollectionsWithPrefix', '')
        );
    }

    /**
     * Fetch credential settings.
     *
     * @param array $conf
     */
    protected function setupCredentials(array $conf)
    {
        $this->uri                    = Util\Arr::getValue($conf, 'uri', '');
        $this->host                   = Util\Arr::getValue($conf, 'host', '');
        $this->user                   = Util\Arr::getValue($conf, 'user', '');
        $this->password               = Util\Arr::getValue($conf, 'password', '');
        $this->authenticationDatabase = Util\Arr::getValue($conf, 'authenticationDatabase', '');
    }

    /**
     * Validate source setup
     *
     * @throws \phpbu\App\Exception
     */
    protected function setupValidation()
    {
        if (empty($this->uri)) {
            return;
        }

        // If uri is set, cannot set other configurations
        foreach (['user', 'host', 'password', 'authenticationDatabase', 'databases'] as $attr) {
            if (!empty($this->{$attr})) {
                throw new Exception("cannot specify $attr and uri");
            }
        }
    }

    /**
     * Execute the backup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result) : Status
    {
        // setup dump location and execute the dump
        $mongodump = $this->execute($target);

        $result->debug($mongodump->getCmdPrintable());

        if (!$mongodump->isSuccessful()) {
            throw new Exception('mongodump failed: ' . $mongodump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the Mongodump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Mongodump($this->pathToMongodump);
        $executable->dumpToDirectory($this->getDumpDir($target))
                   ->useIpv6($this->useIPv6)
                   ->useUri($this->uri)
                   ->useHost($this->host)
                   ->credentials($this->user, $this->password, $this->authenticationDatabase)
                   ->dumpDatabases($this->databases)
                   ->dumpCollections($this->collections)
                   ->excludeCollections($this->excludeCollections)
                   ->excludeCollectionsWithPrefix($this->excludeCollectionsWithPrefix);
        return $executable;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        return Status::create()->uncompressedDirectory($this->getDumpDir($target));
    }

    /**
     * Get the MongoDB dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target) : string
    {
        return $target->getPath()->getPath() . '/dump';
    }
}
