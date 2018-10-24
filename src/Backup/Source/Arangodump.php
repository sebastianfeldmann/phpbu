<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

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
class Arangodump extends SimulatorExecutable implements Simulator
{
    /**
     * Path to arangodump command.
     *
     * @var string
     */
    private $pathToArangodump;

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
     * @var bool
     */
    private $includeSystemCollections;

    /**
     * Restrict the dump to these collections
     * --collection
     *
     * @var array
     */
    private $collections;

    /**
     * Do not ask for the username and password when connecting to the server.
     * This does not control whether the server requires authentication.
     * -- disable-authentication
     *
     * @var bool
     */
    private $disableAuthentication;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupSourceData($conf);

        $this->pathToArangodump      = Util\Arr::getValue($conf, 'pathToArangodump', '');
        $this->endpoint              = Util\Arr::getValue($conf, 'endpoint', '');
        $this->username              = Util\Arr::getValue($conf, 'username', '');
        $this->password              = Util\Arr::getValue($conf, 'password', '');
        $this->database              = Util\Arr::getValue($conf, 'database', '');
        $this->disableAuthentication = Util\Str::toBoolean(Util\Arr::getValue($conf, 'disableAuthentication'), false);
    }

    /**
     * Get collections and data to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->dumpData                  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dumpData'), false);
        $this->collections               = Util\Str::toList(Util\Arr::getValue($conf, 'collections'));
        $this->includeSystemCollections  = Util\Str::toBoolean(
            Util\Arr::getValue($conf, 'includeSystemCollections'),
            false
        );
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
        $arangodump = $this->execute($target);

        $result->debug($arangodump->getCmdPrintable());

        if (!$arangodump->isSuccessful()) {
            throw new Exception('arangodump failed: ' . $arangodump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the arangodump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Arangodump($this->pathToArangodump);
        $executable->useEndpoint($this->endpoint)
                   ->credentials($this->username, $this->password)
                   ->dumpDatabase($this->database)
                   ->dumpCollections($this->collections)
                   ->disableAuthentication($this->disableAuthentication)
                   ->includeSystemCollections($this->includeSystemCollections)
                   ->dumpData($this->dumpData)
                   ->dumpTo($this->getDumpDir($target));
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
     * Get the ArangoDB dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target) : string
    {
        return $target->getPath()->getPath() . '/dump';
    }
}
