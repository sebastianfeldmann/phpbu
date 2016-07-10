<?php
namespace phpbu\App\Backup;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Util;

/**
 * Rsync trait.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.1.4
 */
trait Rsync
{
    /**
     * Tar Executable
     *
     * @var \phpbu\App\Cli\Executable\Tar
     */
    protected $executable;

    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToRsync;

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
     * Remove deleted files.
     *
     * @var bool
     */
    protected $delete;

    protected function setupRsync(array $conf)
    {
        $this->pathToRsync = Util\Arr::getValue($conf, 'pathToRsync');

        if (Util\Arr::isSetAndNotEmptyString($conf, 'args')) {
            $this->args = $conf['args'];
        } else {
            if (!Util\Arr::isSetAndNotEmptyString($conf, 'path')) {
                throw new Exception('option \'path\' is missing');
            }
            $this->path      = Util\Str::replaceDatePlaceholders($conf['path']);
            $this->user      = Util\Arr::getValue($conf, 'user');
            $this->host      = Util\Arr::getValue($conf, 'host');
            $this->excludes  = Util\Str::toList(Util\Arr::getValue($conf, 'exclude', ''), ':');
            $this->delete    = Util\Str::toBoolean(Util\Arr::getValue($conf, 'delete', ''), false);
            $this->isDirSync = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dirsync', ''), false);
        }
    }

    /**
     * Setup the Executable to run the 'rsync' command.
     *
     * @param  \phpbu\App\Backup\Target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Rsync($this->pathToRsync);
            if (!empty($this->args)) {
                $this->executable->useArgs(
                    Util\Str::replaceTargetPlaceholders(
                        $this->args,
                        $target->getPathnamePlain()
                    )
                );
            } else {
                $this->configureExecutable($this->executable, $target);
            }
        }
        return $this->executable;
    }

    /**
     * Configure the executable if no costim args are used.
     *
     * @param \phpbu\App\Cli\Executable\Rsync $exec
     * @param \phpbu\App\Backup\Target        $target
     */
    abstract protected function configureExecutable(Executable\Rsync $exec, Target $target);

    /**
     * Return rsync location.
     *
     * @param  \phpbu\App\Backup\Target
     * @return string
     */
    protected function getRsyncLocation(Target $target)
    {
        return $this->isDirSync ? $target->getPath() : $target->getPathname();
    }
}
