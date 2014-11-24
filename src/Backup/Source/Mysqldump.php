<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\Backup\Cli;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Util;

/**
 * Mysqldump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump implements Source
{
    /**
     * Executor to run the mysqldump shell commands.
     *
     * @var \phpbu\Cli\Exec
     */
    private $exec;

    /**
     * Configuration
     *
     * @var array
     */
    private $conf;

    /**
     * Setup.
     *
     * @see    \phpbu\Backup\Source
     * @param  array                $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->conf = $conf;
    }

    /**
     *
     * @param  \phpbu\App\Target $target
     * @param  \phpbu\App\Result $result
     * @return \phpbu\App\Result
     */
    public function backup(Target $target, Result $result)
    {
        $host       = 'localhost';
        $user       = $_SERVER['USER'];
        $password   = null;
        $datbases   = array();
        $this->exec = new Cli\Exec();
        $this->exec->setTarget($target);

        $path      = isset($this->conf['pathToMysqldump']) ? $this->conf['pathToMysqldump'] : null;
        $mysqldump = Util\Cli::detectCmdLocation(
            'mysqldump',
            $path,
            array(
                '/usr/local/mysql/bin/mysqldump', // Mac OS X
                '/usr/mysql/bin/mysqldump'        // Linux
            )
        );

        $cmd = new Cli\Cmd($mysqldump);
        $this->exec->addCommand($cmd);

        if (!empty($this->conf['user'])) {
            $user = $this->conf['user'];
            $cmd->addOption('--user', $user);
        }
        if (!empty($this->conf['password'])) {
            $password = $this->conf['password'];
            $cmd->addOption('--password', $password);
        }
        if (!empty($this->conf['host'])) {
            $host = $this->conf['host'];
            $cmd->addOption('--host', $host);
        }

        if (!empty($this->conf['quick']) && Util\String::toBoolean($this->conf['quick'], false)) {
            $cmd->addOption('-q');
        }
        if (!empty($this->conf['compress']) && Util\String::toBoolean($this->conf['compress'], false)) {
            $cmd->addOption('-C');
        }
        if (!empty($this->conf['tables'])) {
            foreach ($tables as $table) {
                $cmd->addOption('--tables', $this->conf['tables']);
            }
        } else {
            if (!empty($this->conf['databases'])) {
                if (empty($this->conf['databases']) || $this->conf['databases'] == '__ALL__') {
                    $cmd->addOption('--all-databases');
                } else {
                    $databases = explode(',', $this->conf['databases']);
                    $cmd->addOption('--databases', $databases);
                }
            }
        }

        // validate mysql connection
        if (!empty($this->conf['validateConnection'])
         && Util\String::toBoolean($this->conf['validateConnection'], false)) {
            if (!$this->canConnect($host, $user, $password, $databases)) {
                throw new Exception('Can\'t connect to mysql server');
            }
        }

        if (!empty($this->conf['ignoreTables'])) {
            $tables = explode(' ', $this->conf['ignoreTables']);
            foreach ($tables as $table) {
                $cmd->addOption('--ignore-table', $table);
            }
        }
        if (!empty($this->conf['structureOnly'])) {
            if ($this->conf['structureOnly'] == '__ALL__') {
                $cmd->addOption('--no-data');
            } else {
                $tables = explode(',', $this->conf['structureOnly']);
                $cmd2   = clone($cmd);
                $cmd->addOption('--no-data');
                foreach ($tables as $table) {
                    $cmd2->addOption('--ignore-table', $table);
                }
                $cmd2->addOption('--skip-add-drop-table');
                $cmd2->addOption('--no-create-db');
                $cmd2->addOption('--no-create-info');
                $this->exec->addCommand($cmd2);
            }
        }
        $r = $this->exec->execute();

        $result->debug($r->getCmd());

        if (!$r->wasSuccessful()) {
            // cleanup possible target
            if (file_exists((string) $target)) {
                $result->debug('unlink defective file');
                unlink((string) $target);
            }
            throw new Exception('mysqldump failed');
        }

        return $result;
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
    public function canConnect($host, $user, $password = null, array $databases = array())
    {
        // no special database given all-databases a requested
        // use 'mysql' because is has to exist
        if (empty($databases)) {
            $databases[] = 'mysql';
        }

        // check all databases
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
