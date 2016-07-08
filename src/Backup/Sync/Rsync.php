<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Sync;
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
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.0
 */
class Rsync extends Cli implements Simulator
{
    /**
     * Path to rsync binary.
     *
     * @var string
     */
    protected $pathToRsync;

    /**
     * Raw args
     *
     * @var string
     */
    protected $args;

    /**
     * Remote username
     *
     * @var string
     */
    protected $user;

    /**
     * Target host
     *
     * @var string
     */
    protected $host;

    /**
     * Target path
     *
     * @var string
     */
    protected $path;

    /**
     * Files to ignore, extracted from config string separated by ":"
     *
     * @var array
     */
    protected $excludes;

    /**
     * Should only the created backup be synced or the complete directory
     *
     * @var boolean
     */
    protected $isDirSync;

    /**
     * Remove deleted files remotely as well
     *
     * @var boolean
     */
    protected $delete;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $options)
    {
        $this->pathToRsync = Util\Arr::getValue($options, 'pathToRsync');

        if (Util\Arr::isSetAndNotEmptyString($options, 'args')) {
            $this->args = $options['args'];
        } else {
            if (!Util\Arr::isSetAndNotEmptyString($options, 'path')) {
                throw new Exception('option \'path\' is missing');
            }
            $this->path = Util\Str::replaceDatePlaceholders($options['path']);

            if (Util\Arr::isSetAndNotEmptyString($options, 'user')) {
                $this->user = $options['user'];
            }
            if (Util\Arr::isSetAndNotEmptyString($options, 'host')) {
                $this->host = $options['host'];
            }

            $this->excludes  = Util\Str::toList(Util\Arr::getValue($options, 'exclude', ''), ':');
            $this->delete    = Util\Str::toBoolean(Util\Arr::getValue($options, 'delete', ''), false);
            $this->isDirSync = Util\Str::toBoolean(Util\Arr::getValue($options, 'dirsync', ''), false);
        }
    }

    /**
     * (non-PHPDoc)
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

        if (!$rsync->wasSuccessful()) {
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
            . $this->getExecutable($target)->getCommandLine()
        );
    }

    /**
     * Create the Exec to run the 'rsync' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Rsync($this->pathToRsync);
            if (!empty($this->args)) {
                $this->executable->useArgs(Util\Str::replaceTargetPlaceholders($this->args, $target->getPathname()));
            } else {
                $this->executable->fromPath($this->getSyncSource($target))
                     ->toHost($this->host)
                     ->toPath($this->path)
                     ->toUser($this->user)
                     ->compressed(!$target->shouldBeCompressed())
                     ->removeDeleted($this->delete)
                     ->exclude($this->excludes);
            }
        }
        return $this->executable;
    }

    /**
     * Return sync source.
     *
     * @param  \phpbu\App\Backup\Target
     * @return string
     */
    public function getSyncSource(Target $target)
    {
        return $this->isDirSync ? $target->getPath() : $target->getPathname();
    }
}
