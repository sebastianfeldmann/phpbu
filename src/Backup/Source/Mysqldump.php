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
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump extends Cli implements Source
{
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
     * @param  \phpbu\Backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @return \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        $host      = 'localhost';
        $user      = $_SERVER['USER'];
        $password  = null;
        $databases = array();
        $exec      = new Exec();
        $path      = isset($this->conf['pathToMysqldump']) ? $this->conf['pathToMysqldump'] : null;
        $mysqldump = Util\Cli::detectCmdLocation(
            'mysqldump',
            $path,
            array(
                '/usr/local/mysql/bin/mysqldump', // Mac OS X
                '/usr/mysql/bin/mysqldump'        // Linux
            )
        );

        $cmd = new Cmd($mysqldump);
        $exec->addCommand($cmd);

        // no std error unless it is activated
        if (empty($this->conf['showStdErr']) || !Util\String::toBoolean($this->conf['showStdErr'], false)) {
            $cmd->silence();
        }
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
            $tables = explode(',', $this->conf['tables']);
            $cmd->addOption('--tables', $tables);
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
                $exec->addCommand($cmd2);
            }
        }
        $r = $this->execute($exec, $target);

        $result->debug($r->getCmd());

        if (!$r->wasSuccessful()) {
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
        // no special database configured
        if (empty($databases)) {
            $databases[] = null;
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
