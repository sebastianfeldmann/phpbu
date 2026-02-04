<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Rsync as RsyncTrait;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Rsync source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.2.0
 */
class Rsync extends SimulatorExecutable implements Simulator
{
    use RsyncTrait;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupRsync($conf);
    }

    /**
     * Execute the backup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result) : Status
    {
        $rsync = $this->execute($target);
        $result->debug($rsync->getCmd());

        if (!$rsync->isSuccessful()) {
            throw new Exception('rsync failed:' . $rsync->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Setup the Executable to run the 'rsync' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Rsync($this->pathToRsync);
        if (!empty($this->args)) {
            $executable->useArgs(
                Util\Path::replaceTargetPlaceholders(
                    $this->args,
                    $target->getPathnamePlain()
                )
            );
        } else {
            $executable->fromHost($this->host)
                       ->fromUser($this->user)
                       ->fromPath($this->path)
                       ->toPath($this->getRsyncLocation($target, true))
                       ->removeDeleted($this->delete)
                       ->exclude($this->excludes);
        }
        return $executable;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        $status = Status::create();
        if (!$this->isDirSync) {
            $targetFile = $target->getPathnamePlain();
            is_dir($targetFile) ? $status->uncompressedDirectory($targetFile) : $status->uncompressedFile($targetFile);
        }
        return $status;
    }
}
