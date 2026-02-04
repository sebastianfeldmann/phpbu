<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Rsync as RsyncTrait;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Rsync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.1.0
 */
class Rsync extends Cli implements Simulator
{
    use RsyncTrait;

    /**
     * Setup the rsync sync.
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $options)
    {
        try {
            $this->setupRsync($options);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Execute the sync.
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        if ($this->args) {
            // pro mode define all arguments yourself
            // WARNING! no escaping is done by phpbu
            $result->debug('WARNING: phpbu uses your rsync args without escaping');
        }
        $rsync = $this->execute($target);

        $result->debug($rsync->getCmd());

        if (!$rsync->isSuccessful()) {
            throw new Exception('rsync failed: ' . $rsync->getStdErr());
        }
    }

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'sync backup with rsync' . PHP_EOL
            . $this->getExecutable($target)->getCommandPrintable()
        );
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
                    $target->getPathname()
                )
            );
        } else {
            $executable->fromPath($this->getRsyncLocation($target))
                       ->usePassword($this->password)
                       ->usePasswordFile($this->passwordFile)
                       ->toHost($this->host)
                       ->toPath($this->path)
                       ->toUser($this->user)
                       ->compressed(!$target->shouldBeCompressed())
                       ->removeDeleted($this->delete)
                       ->exclude($this->excludes);
        }
        return $executable;
    }
}
