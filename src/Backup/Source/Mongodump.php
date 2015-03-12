<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli\Binary;
use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class Mongodump extends Binary implements Source
{
    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

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
     * Use php to validate the MongoDB connection
     *
     * @var boolean
     */
    private $validateConnection;

    /**
     * Tar source to compress MongoDB dump directory
     *
     * @var \phpbu\App\Backup\Source\Tar
     */
    private $tar;

    /**
     * (No PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupMongodump($conf);
        $this->setupSourceData($conf);

        // credentials
        $this->host                   = Util\Arr::getValue($conf, 'host');
        $this->user                   = Util\Arr::getValue($conf, 'user');
        $this->password               = Util\Arr::getValue($conf, 'password');
        $this->authenticationDatabase = Util\Arr::getValue($conf, 'authenticationDatabase');

        // config & validation
        $this->useIPv6            = Util\String::toBoolean(Util\Arr::getValue($conf, 'ipv6', ''), false);
        $this->showStdErr         = Util\String::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->validateConnection = Util\String::toBoolean(Util\Arr::getValue($conf, 'validateConnection', ''), false);
    }

    /**
     * Search for Mongodump command.
     *
     * @param array $conf
     */
    protected function setupMongodump(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = Util\Cli::detectCmdLocation('mongodump', Util\Arr::getValue($conf, 'pathToMongodump'));
        }
    }

    /**
     * Fetch databases and collections to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->databases                    = Util\String::toList(Util\Arr::getValue($conf, 'databases'));
        $this->collections                  = Util\String::toList(Util\Arr::getValue($conf, 'collections'));
        $this->excludeCollections           = Util\String::toList(Util\Arr::getValue($conf, 'excludeCollections'));
        $this->excludeCollectionsWithPrefix = Util\String::toList(Util\Arr::getValue($conf, 'excludeCollectionsWithPrefix'));
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        if ($this->validateConnection) {
            $this->checkConnection($this->host, $this->user, $this->password, $this->databases);
        }

        $exec      = $this->getExec($target);
        $mongodump = $this->execute($exec);

        $result->debug($mongodump->getCmd());

        if (!$mongodump->wasSuccessful()) {
            throw new Exception('Mongodump failed');
        }

        try {
            $tar = $this->getTar($target);
            $tar->backup($target, $result);
            $result->debug('remove dump directory');
        } catch (\Exception $e) {
            throw new Exception('Failed to \'tar\' Mongodump directory', 1, $e);
        }
        return $result;
    }

    /**
     * Create the Exec to run the Mongodump command
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Cli\Exec
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $dump       = $this->getDumpDir($target);
            $this->exec = new Exec();
            $cmd        = new Cmd($this->binary);
            $this->exec->addCommand($cmd);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $cmd->silence();
                // i kill you
            }

            $cmd->addOption('--out', $dump, ' ');
            $this->addOptionIfNotEmpty($cmd, '--ipv6', $this->useIPv6, false);
            $this->addOptionIfNotEmpty($cmd, '--host', $this->host, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--user', $this->user, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--password', $this->password, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--authenticationDatabase', $this->authenticationDatabase, true, ' ');

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

            $this->addOptionIfNotEmpty($cmd, '--excludeCollection', $this->excludeCollections);
            $this->addOptionIfNotEmpty($cmd, '--excludeCollectionWithPrefix', $this->excludeCollectionsWithPrefix);
        }

        return $this->exec;
    }

    /**
     * Tar setter, mostly for test purposes.
     *
     * @param \phpbu\App\Backup\Source\Tar $tar
     */
    public function setTar(Tar $tar)
    {
        $this->tar = $tar;
    }

    /**
     * Create a Tar backup source to compress the MongoDB dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Tar
     * @throws \phpbu\App\Exception
     */
    public function getTar(Target $target)
    {
        if (null == $this->tar) {
            $this->tar = new Tar();
            $this->tar->setup(
                array(
                    'path'      => $this->getDumpDir($target),
                    'removeDir' => 'true',
                )
            );
        }
        return $this->tar;
    }

    /**
     * Get the MongoDB dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target)
    {
        return $target->getPath() . '/dump';
    }

    /**
     * Test MongoDB connection.
     *
     * @param  string $host
     * @param  string $user
     * @param  string $password
     * @param  array  $databases
     * @throws \phpbu\App\Exception
     */
    public function checkConnection($host, $user, $password, array $databases = array())
    {
        // ToDo: implement mongo db connection validation
    }
}
