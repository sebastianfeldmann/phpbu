<?php
namespace phpbu\Backup\Source;

use phpbu\App\Result;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Cli;
use phpbu\Util;
use RuntimeException;

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
     * @var phpbu\Cli\Exec
     */
    private $exec;

    /**
     * Setup.
     *
     * @see    phpbu\Backup\Source
     * @param  phpbu\Backup\Target $target
     * @param  array               $conf
     * @throws RuntimeException
     */
    public function setup(Target $target, array $conf = array())
    {
        $user       = $_SERVER['USER'];
        $password   = null;
        $host       = 'localhost';
        $this->exec = new Cli\Exec();
        $this->exec->setTarget($target);

        $path      = isset($conf['pathToMysqldump']) ? $conf['pathToMysqldump'] : null;
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

        if (!empty($conf['user'])) {
            $user = $conf['user'];
            $cmd->addOption('--user', $user);
        }
        if (!empty($conf['password'])) {
            $password = $conf['password'];
            $cmd->addOption('--password', $password);
        }
        if (!empty($conf['host'])) {
            $host = $conf['host'];
            $cmd->addOption('--host', $host);
        }

        $this->testMysqlConnection($user, $password, $host);

        if (!empty($conf['quick']) && Util\String::toBoolean($conf['quick'], false)) {
            $cmd->addOption('-q');
        }
        if (!empty($conf['compress']) && Util\String::toBoolean($conf['compress'], false)) {
            $cmd->addOption('-C');
        }
        if (!empty($conf['tables'])) {
            foreach ($tables as $table) {
                $cmd->addOption('--tables', $conf['tables']);
            }
        } else {
            if (!empty($conf['databases'])) {
                if (empty($conf['databases']) || $conf['databases'] == '__ALL__') {
                    $cmd->addOption('--all-databases');
                } else {
                    $databases = explode(',', $conf['databases']);
                    $cmd->addOption('--databases', $databases);
                }
            }
        }
        if (!empty($conf['ignoreTables'])) {
            $tables = explode(' ', $conf['ignoreTables']);
            foreach ($tables as $table) {
                $cmd->addOption('--ignore-table', $table);
            }
        }
        if (!empty($conf['structureOnly'])) {
            if ($conf['structureOnly'] == '__ALL__') {
                $cmd->addOption('--no-data');
            } else {
                $tables = explode(',', $conf['structureOnly']);
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
    }

    /**
     * Test mysql connection.
     *
     * @param  string $user
     * @param  string $password
     * @param  string $host
     * @return boolean
     * @throws RuntimeException
     */
    public function testMysqlConnection($user = null, $password = null, $host = null)
    {
        // TODO: test mysql connection
        // if some mysql extension is loaded (mysql, mysqli, pdo)
        // no user given get os user
        // try to connect to database
        return true;
    }

    /**
     *
     * @param  phpbu\App\Result $result
     * @return phpbu\App\Result
     */
    public function backup(Result $result)
    {
        $r = $this->exec->execute();

        echo $r->getCmd() . PHP_EOL;

        return $result;
    }
}
