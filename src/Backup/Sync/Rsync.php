<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Result;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;
use phpbu\Util\Arr;
use phpbu\Util\Cli as CliUtil;
use phpbu\Util\String;

/**
 * Rsync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.0
 */
class Rsync extends Cli implements Sync
{
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
     * @see    \phpbu\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (Arr::isSetAndNotEmptyString($config, 'args')) {
            $this->args = $config['args'];
        } else {
            if (!Arr::isSetAndNotEmptyString($config, 'path')) {
                throw new Exception('option \'path\' is missing');
            }
            $this->path = String::replaceDatePlaceholders($config['path']);

            if (isset($config['user'])) {
                $this->user = $config['user'];
            }
            if (isset($config['host'])) {
                $this->host = $config['host'];
            }

            $this->excludes  = array_map('trim', explode(':', Arr::getValue($config, 'exclude', '')));
            $this->delete    = String::toBoolean(Arr::getValue($config, 'delete', ''), false);
            $this->isDirSync = String::toBoolean(Arr::getValue($config, 'dirsync', ''), false);
        }
    }

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\Backup\Sync::sync()
     * @param  \phpbu\backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $rsync = new Cmd(CliUtil::detectCmdLocation('rsync'));

        if ($this->args) {
            // pro mode define all arguments yourself
            // WARNING! no escaping is done by phpbu
            $result->debug('WARNING: phpbu uses your rsync args without escaping');
            $rsync->addOption($this->replaceTargetPlaceholder($this->args, $target));
        } else {
            // std err > dev null
            $rsync->silence();

            $targetFile = $target->getPathnameCompressed();
            $targetDir  = dirname($targetFile);

            // use archive mode, verbose and compress if not already done
            $options = '-av' . ($target->shouldBeCompressed() ? '' : 'z');
            $rsync->addOption($options);

            if (count($this->excludes)) {
                foreach ($this->excludes as $ex) {
                    $rsync->addOption('--exclude', $ex);
                }
            }

            // source handling
            if ($this->isDirSync) {
                // sync the whole folder
                // delete remote files as well?
                if ($this->delete) {
                    $rsync->addOption('--delete');
                }
                $rsync->addArgument($targetDir);
            } else {
                // sync just the created backup
                $rsync->addArgument($targetFile);
            }

            // target handling
            $syncTarget = '';
            // remote host
            if (null !== $this->host) {
                // remote user
                if (null !== $this->user) {
                    $syncTarget .= $this->user . '@';
                }
                $syncTarget .= $this->host . ':';
            }
            // remote path
            $syncTarget .= $this->path;

            $rsync->addArgument($syncTarget);
        }
        // add some debug output
        $result->debug((string) $rsync);

        $this->execute($rsync);
    }
}
