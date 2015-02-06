<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Result;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Cli\Exec;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;
use phpbu\Util;

/**
 * Rsync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
     * Files to ignore, extracted from config string seperated by ":"
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
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::setup()
     */
    public function setup(array $config)
    {
        if (isset($config['args'])) {
            $this->args = $config['args'];
        } else {
            if (empty($config['path'])) {
                throw new Exception('option \'path\' is missing');
            }
            $this->path = $config['path'];

            if (isset($config['user'])) {
                $this->user = $config['user'];
            }
            if (isset($config['host'])) {
                $this->host = $config['host'];
            }

            $this->excludes  = isset($config['exclude'])
                             ? array_map('trim', explode(':', $config['exclude']))
                             : array();
            $this->delete    = isset($config['delete'])
                             ? Util\String::toBoolean($config['delete'], false)
                             : false;
            $this->isDirSync = isset($config['dirsync'])
                             ? Util\String::toBoolean($config['dirsync'], false)
                             : false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpbu\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result)
    {
        $rsync = new Cmd(Util\Cli::detectCmdLocation('rsync'));

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

            // use archive mode, verbose and compress if not allready done
            $options = '-av' . ( $target->shouldBeCompressed() ? '' : 'z' );
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
            // remote user
            if (null !== $this->user) {
                $syncTarget .= $this->user . '@';
            }
            // remote host
            if (null !== $this->host) {
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
