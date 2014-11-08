<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\Backup\Compressor;
use phpbu\Backup\Runner;
use phpbu\Backup\Source;
use phpbu\Backup\Target;

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
        $this->runner = new Runner\Cli();
        $this->runner->setTarget($target);

        $cmd = new Runner\Cli\Cmd('mysqldump');

        $this->runner->addCommand($cmd);

        if (!empty($conf['quick'])) {
            $cmd->addOption('-q');
        }
        if (!empty($conf['tables'])) {
            foreach ($tables as $table) {
                $cmd->addOption('--tables=' . $conf['tables']);
            }
        } else {
            if (!empty($conf['databases'])) {
                if ($conf['databases'] == '__ALL__') {
                    $cmd->addOption('--all-databases');
                } else {
                    $cmd->addOption('--databases ' . $conf['databases']);
                }
            }
        }
        if (!empty($conf['ignoreTables'])) {
            $tables = explode(' ', $conf['ignoreTables']);
            foreach ($tables as $table) {
                $cmd->addOption('--ignore-table=' . $table);
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
                    $cmd2->addOption('--ignore-table=' . $table);
                }
                $cmd2->addOption('--skip-add-drop-table');
                $cmd2->addOption('--no-create-db');
                $cmd2->addOption('--no-create-info');
                $this->runner->addCommand($cmd2);
            }
        }
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
