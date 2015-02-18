<?php
namespace phpbu\Backup\Sync;

use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Cli\Exec;
use phpbu\Backup\Target;

/**
 * Cli
 *
 * Baseclass for all cli based sync tools e.g. 'rsync'
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.0
 */
abstract class Cli
{
    /**
     * Executes a cli command
     *
     * @param  \phpbu\Backup\Cli\Cmd
     * @throws \phpbu\Backup\Sync\Exception
     */
    protected function execute(Cmd $command)
    {
        $exec = new Exec();
        $exec->addCommand($command);

        /* @var $res \phpbu\Backup\Cli\Result */
        $res = $exec->execute();
        if ($res->getCode()) {
            throw new Exception('sync failed: ' . PHP_EOL . $res->getOutputAsString());
        }
    }

    /**
     * Replaces %TARGET_DIR% and %TARGET_FILE%
     *
     * @param  string $string
     * @param  Target $target
     * @return string
     */
    protected function replaceTargetPlaceholder($string, Target $target)
    {
        $targetFile = $target->getPathnameCompressed();
        $targetDir  = dirname($targetFile);
        $search     = array('%TARGET_DIR%', '%TARGET_FILE%');
        $replace    = array($targetDir, $targetFile);
        return str_replace($search, $replace, $string);
    }
}
