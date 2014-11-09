<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\Backup\Compressor;
use phpbu\Backup\Runner;
use phpbu\Backup\Source;
use phpbu\Backup\Target;
use phpbu\Util\String;
use RuntimeException;

/**
 * Mysqldump source class.
 *
 * @package    phpbu
 * @subpackage backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump implements Source
{
    /**
     * Runner to execute shell commands.
     *
     * @var phpbu\Backup\Runner\Cli
     */
    private $runner;

    /**
     * Setup.
     *
     * @see    phpbu\Backup\Source
     * @param  phpbu\Backup\Target $target
     * @param  array               $conf
     * @throws phpbu\App\Exception
     */
    public function setup(Target $target, array $conf = array())
    {
        $user         = $_SERVER['USER'];
        $password     = null;
        $host         = 'localhost';
        $this->runner = new Runner\Cli();
        $this->runner->setTarget($target);

        $path      = isset($conf['pathToMysqldump']) ? $conf['pathToMysqldump'] : null;
        $mysqldump = $this->detectMysqldumpLocation($path);
        $cmd       = new Runner\Cli\Cmd($mysqldump);

        $this->runner->addCommand($cmd);

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

        if (!empty($conf['quick']) && String::toBoolean($conf['quick'], false)) {
            $cmd->addOption('-q');
        }
        if (!empty($conf['compress']) && String::toBoolean($conf['compress'], false)) {
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
                $this->runner->addCommand($cmd2);
            }
        }
    }

    /**
     * Detect the 'mysqldump' location.
     *
     * @param  string $path     Directory where mysqldumo should be located
     * @return string           Absolute path to mysqldump command
     * @throws RuntimeException
     */
    public function detectMysqldumpLocation($path = null)
    {
        // explicit path given, so check it out
        if (null !== $path) {
            $mysqldump = $path . DIRECTORY_SEPARATOR . 'mysqldump';
            if (!is_executable($mysqldump)) {
                throw new RuntimeException(sprintf('wrong path specified for \'mysqldump\': %s', $path));
            }
            return $mysqldump;
        }

        // on nx systems use 'which' command.
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $mysqldump = `which mysqldump`;
            if (is_executable($mysqldump)) {
                return $mysqldump;
            }
            // try to find mysql command.
            $mysqldump = dirname(`which mysql`) . "/mysqldump";
            if (is_executable($mysqldump)) {
                return $mysqldump;
            }
        }

        // checking environment variable.
        $pathList = explode(PATH_SEPARATOR, $_SERVER['PATH']);
        foreach ($pathList as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // some more pathes we came accross.
        $pathList = array(
            '/usr/local/mysql/bin/mysqldump', // Mac OS X
            '/usr/mysql/bin/mysqldump'        // Linux
        );
        foreach ($pathList as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }
        throw new RuntimeException('\'mysqldump\' was nowhere to be found please specify the correct path');
    }

    /**
     *
     * @return phpbu\Backup\Runner
     */
    public function getRunner()
    {
        return $this->runner;
    }
}