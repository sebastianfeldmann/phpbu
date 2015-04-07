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
 * Arangodump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Arangodump extends Binary implements Source
{
    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

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
    private $collections;

    /**
     * Do not ask for the username and password when connecting to the server.
     * This does not control whether the server requires authentication.
     * -- disable-authentication
     *
     * @var boolean
     */
    private $disableAuthentication;

    /**
     * Tar source to compress ArangoDB dump directory
     *
     * @var \phpbu\App\Backup\Source\Tar
     */
    private $tar;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupArangodump($conf);
        $this->setupSourceData($conf);

        $this->endpoint              = Util\Arr::getValue($conf, 'endpoint');
        $this->username              = Util\Arr::getValue($conf, 'username');
        $this->password              = Util\Arr::getValue($conf, 'password');
        $this->database              = Util\Arr::getValue($conf, 'database');
        $this->showStdErr            = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr'), false);
        $this->disableAuthentication = Util\Str::toBoolean(Util\Arr::getValue($conf, 'disableAuthentication'), false);
    }

    /**
     * Search for arangodump command.
     *
     * @param array $conf
     */
    protected function setupArangodump(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = $this->detectCommand('arangodump', Util\Arr::getValue($conf, 'pathToArangodump'));
        }
    }

    /**
     * Get collections and data to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->dumpData                  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dumpData'), false);
        $this->includeSystemCollections  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'includeSystemCollections'), false);
        $this->collections               = Util\Str::toList(Util\Arr::getValue($conf, 'collections'));
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        $exec       = $this->getExec($target);
        $arangodump = $this->execute($exec);

        $result->debug($arangodump->getCmd());

        if (!$arangodump->wasSuccessful()) {
            throw new Exception('arangodump failed');
        }

        return Status::create()->uncompressed()->dataPath($this->getDumpDir($target));
    }

    /**
     * Create the Exec to run the mysqldump command.
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

            $this->addOptionIfNotEmpty($cmd, '--server.username', $this->username, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--server.password', $this->password, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--server.endpoint', $this->endpoint, true, ' ');
            $this->addOptionIfNotEmpty($cmd, '--server.database', $this->database, true, ' ');

            if (count($this->collections)) {
                foreach($this->collections as $collection){
                    $cmd->addOption('--collection', $collection, ' ');
                }
            }

            if($this->disableAuthentication){
                $cmd->addOption('--server.disable-authentication', var_export($this->disableAuthentication, true), ' ');
            }

            if ($this->includeSystemCollections) {
                $cmd->addOption('--include-system-collections', var_export($this->includeSystemCollections, true), ' ');
            }

            if ($this->dumpData) {
                $cmd->addOption('--dump-data', var_export($this->dumpData, true), ' ');
            }

            $cmd->addOption('--output-directory', $dump, ' ');
        }

        return $this->exec;
    }

    /**
     * Get the ArangoDB dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target)
    {
        return $target->getPath() . '/dump';
    }
}