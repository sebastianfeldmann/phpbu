<?php
namespace phpbu\Backup\Sync;

use phpbu\Backup\Cli\Command;
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
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.0
 */
abstract class Cli
{
    /**
     * Executes a cli command
     *
     * @return \phpbu\Cli\Result
     * @throws \phpbu\Backup\Sync\Exception
     */
    protected function execute(Command $command)
    {
        $exec = new Exec();
        $exec->addCommand($cmd);

        $res = $exec->execute();
        if ($res->getCode()) {
            throw new Exception('sync failed: ' . PHP_EOL . $res->getOutput());
        }
    }

    /**
     * Replaces %TARGET_DIR% and %TARGET_FILE%
     *
     * @param  string $args
     * @param  Target $target
     * @return string
     */
    protected function replaceTargetPlaceholder($string, Target $target)
    {
        $targetFile = $target->getFilenameCompressed();
        $targetDir  = dirname($targetFile);
        $search     = array('%TARGET_DIR%', '%TARGET_FILE%');
        $replace    = array($targetDir, $targetFile);
        return str_replace($search, $replace, $string);
    }
}
