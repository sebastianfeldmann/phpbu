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
class Mysqldump extends Binary implements Source
{
    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

    /**
     * Host to connect to
     * --host <hostname>
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
     * List of tables to backup
     * --tables array of strings
     *
     * @var array
     */
    private $tables;

    /**
     * List of databases to backup
     * --databases array of strings
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
     * -q
     *
     * @var boolean
     */
    private $quick;

    /**
     * Use mysqldump with compression
     * -C
     *
     * @var boolean
     */
    private $compress;

    /**
     * Dump only table structures
     * --no-data
     *
     * @var boolean
     */
    private $noData;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupMysqldump($conf);
        $this->setupSourceData($conf);

        $this->host       = Util\Arr::getValue($conf, 'host');
        $this->user       = Util\Arr::getValue($conf, 'user');
        $this->password   = Util\Arr::getValue($conf, 'password');
        $this->showStdErr = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->quick      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'quick', ''), false);
        $this->compress   = Util\Str::toBoolean(Util\Arr::getValue($conf, 'compress', ''), false);
        $this->noData     = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noData', ''), false);
    }

    /**
     * Search for mysqldump command.
     *
     * @param array $conf
     */
    protected function setupMysqldump(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = $this->detectCommand('mysqldump', Util\Arr::getValue($conf, 'pathToMysqldump'));
        }
    }

    /**
     * Get tables and databases to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->tables        = Util\Str::toList(Util\Arr::getValue($conf, 'tables'));
        $this->databases     = Util\Str::toList(Util\Arr::getValue($conf, 'databases'));
        $this->ignoreTables  = Util\Str::toList(Util\Arr::getValue($conf, 'ignoreTables'));
        $this->structureOnly = Util\Str::toList(Util\Arr::getValue($conf, 'structureOnly'));
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
        $exec      = $this->getExec();
        $mysqldump = $this->execute($exec, $target->getPathnamePlain(), $target->getCompressor());

        $result->debug($mysqldump->getCmd());

        if (!$mysqldump->wasSuccessful()) {
            throw new Exception('mysqldump failed');
        }

        return $result;
    }

    /**
     * Create the Exec to run the mysqldump command.
     *
     * @return \phpbu\App\Backup\Cli\Exec
     * @throws Exception
     */
    public function getExec()
    {
        if (null == $this->exec) {
            $this->exec = new Exec();
            $cmd        = new Cmd($this->binary);
            $this->exec->addCommand($cmd);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $cmd->silence();
                // i kill you
            }
            $this->addOptionIfNotEmpty($cmd, '--user', $this->user);
            $this->addOptionIfNotEmpty($cmd, '--password', $this->password);
            $this->addOptionIfNotEmpty($cmd, '--host', $this->host);
            $this->addOptionIfNotEmpty($cmd, '-q', $this->quick, false);
            $this->addOptionIfNotEmpty($cmd, '-C', $this->compress, false);

            if (count($this->tables)) {
                $cmd->addOption('--tables', $this->tables);
            } else {
                if (count($this->databases)) {
                    $cmd->addOption('--databases', $this->databases);
                } else {
                    $cmd->addOption('--all-databases');
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
                    $this->exec->addCommand($cmd2);
                }
            }
        }
        return $this->exec;
    }
}
