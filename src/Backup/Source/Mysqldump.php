<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Cli\Exec;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Util;

/**
 * Mysqldump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump extends Cli implements Source
{
    /**
     * Path to mysqldump command
     *
     * @var string
     */
    private $binary;

    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

    /**
     * Host to connect to
     *
     * @var string
     */
    private $host;

    /**
     * User to connect with
     *
     * @var string
     */
    private $user;

    /**
     * Password to authenticate with
     *
     * @var string
     */
    private $password;

    /**
     * List of tables to backup
     *
     * @var array
     */
    private $tables;

    /**
     * List of databases to backup
     *
     * @var array
     */
    private $databases;

    /**
     * List of tables to ignore
     *
     * @var array
     */
    private $ignoreTables;

    /**
     * List of tables where only the table structure is stored
     *
     * @var array
     */
    private $structureOnly;

    /**
     * Use mysqldump quick mode
     *
     * @var boolean
     */
    private $quick;

    /**
     * Use mysqldump with compression
     *
     * @var boolean
     */
    private $compress;

    /**
     * Dump only table structures
     *
     * @var boolean
     */
    private $noData;

    /**
     * Use php to validate the mysql connection
     *
     * @var boolean
     */
    private $validateConnection;

    /**
     * Setup.
     *
     * @see    \phpbu\Backup\Source
     * @param  array                $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupMysqldump($conf);
        $this->setupSourceData($conf);

        $this->host               = Util\Arr::getValue($conf, 'host');
        $this->user               = Util\Arr::getValue($conf, 'user');
        $this->password           = Util\Arr::getValue($conf, 'password');
        $this->showStdErr         = Util\String::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->quick              = Util\String::toBoolean(Util\Arr::getValue($conf, 'quick', ''), false);
        $this->compress           = Util\String::toBoolean(Util\Arr::getValue($conf, 'compress', ''), false);
        $this->validateConnection = Util\String::toBoolean(Util\Arr::getValue($conf, 'validateConnection', ''), false);
        $this->noData             = Util\String::toBoolean(Util\Arr::getValue($conf, 'noData', ''), false);
    }

    /**
     * Binary setter, mostly for test purposes.
     *
     * @param string $pathToMysqldump
     */
    public function setBinary($pathToMysqldump)
    {
        $this->binary = $pathToMysqldump;
    }

    /**
     * Search for mysqldump command.
     *
     * @param array $conf
     */
    protected function setupMysqldump(array $conf)
    {
        if (empty($this->binary)) {
            $path = isset($conf['pathToMysqldump']) ? $conf['pathToMysqldump'] : null;
            $this->binary = Util\Cli::detectCmdLocation(
                'mysqldump',
                $path,
                array(
                    '/usr/local/mysql/bin/mysqldump', // Mac OS X
                    '/usr/mysql/bin/mysqldump'        // Linux
                )
            );
        }
    }

    /**
     * Get tables and databases to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->tables        = Util\String::toList(Util\Arr::getValue($conf, 'tables'));
        $this->databases     = Util\String::toList(Util\Arr::getValue($conf, 'databases'));
        $this->ignoreTables  = Util\String::toList(Util\Arr::getValue($conf, 'ignoreTables'));
        $this->structureOnly = Util\String::toList(Util\Arr::getValue($conf, 'structureOnly'));
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\Backup\Source
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        $exec      = $this->getExec();
        $cmdResult = $this->execute($exec, $target);

        $result->debug($cmdResult->getCmd());

        if (!$cmdResult->wasSuccessful()) {
            throw new Exception('mysqldump failed');
        }

        return $result;
    }

    /**
     * Create the Exec to run the mysqldump command
     *
     * @return Exec
     * @throws Exception
     */
    public function getExec()
    {
        $exec = new Exec();
        $cmd  = new Cmd($this->binary);
        $exec->addCommand($cmd);

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }
        if (!empty($this->user)) {
            $cmd->addOption('--user', $this->user);
        }
        if (!empty($this->password)) {
            $cmd->addOption('--password', $this->password);
        }
        if (!empty($this->host)) {
            $cmd->addOption('--host', $this->host);
        }
        if ($this->quick) {
            $cmd->addOption('-q');
        }
        if ($this->compress) {
            $cmd->addOption('-C');
        }
        if (count($this->tables)) {
            $cmd->addOption('--tables', $this->tables);
        } else {
            if (count($this->databases)) {
                $cmd->addOption('--databases', $this->databases);
            } else {
                $cmd->addOption('--all-databases');
            }
        }

        if ($this->validateConnection) {
            if (!$this->canConnect($this->host, $this->user, $this->password, $this->databases)) {
                throw new Exception('Can\'t connect to mysql server');
            }
        }

        if (count($this->ignoreTables)) {
            foreach ($this->ignoreTables as $table) {
                $cmd->addOption('--ignore-table', $table);
            }
        }
        if ($this->noData) {
            $cmd->addOption('--no-data');
        } else {
            if (count($this->structureOnly)) {
                $cmd->addOption('--no-data');
                $cmd2 = clone($cmd);
                foreach ($this->structureOnly as $table) {
                    $cmd2->addOption('--ignore-table', $table);
                }
                $cmd2->addOption('--skip-add-drop-table');
                $cmd2->addOption('--no-create-db');
                $cmd2->addOption('--no-create-info');
                $exec->addCommand($cmd2);
            }
        }
        return $exec;
    }

    /**
     * Test mysql connection.
     *
     * @param  string $host
     * @param  string $user
     * @param  string $password
     * @param  array  $databases
     * @return boolean
     * @throws \phpbu\App\Exception
     */
    public function canConnect($host, $user, $password, array $databases = array())
    {
        // no host configured
        if (empty($host)) {
            // localhost by default
            $host = 'localhost';
        }
        // no user configured
        if (empty($user)) {
            if (php_sapi_name() != 'cli') {
                throw new Exception('user is required for connection validation');
            }
            // in cli mode we use the system user as default
            $user = $_SERVER['USER'];
        }
        // no databases configured
        if (empty($databases)) {
            // add the null database to trigger foreach anyway
            $databases[] = null;
        }

        // check all configured databases
        foreach ($databases as $db) {
            $mysqli = @new \mysqli($host, $user, $password, $db);
            if (0 != $mysqli->connect_errno) {
                unset($mysqli);
                return false;
            }

            $mysqli->close();
            unset($mysqli);
        }
        return true;
    }
}
