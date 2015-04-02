<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Cli\Binary;
use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util;

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
class Rsync extends Binary implements Sync
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
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $options)
    {
        $this->setupRsync($options);

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
     * Search for rsync command.
     *
     * @param array $conf
     */
    protected function setupRsync(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = Util\Cli::detectCmdLocation(
                'rsync',
                Util\Arr::getValue($conf, 'pathToRsync')
            );
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
        $exec  = $this->getExec($target);
        $rsync = $this->execute($exec);

        $result->debug($rsync->getCmd());

        if (!$rsync->wasSuccessful()) {
            throw new Exception('rsync failed: ' . PHP_EOL . $rsync->getOutputAsString());
        }
    }

    /**
     * Create the Exec to run the 'rsync' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Cli\Exec
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $this->exec = new Exec();
            $rsync      = new Cmd($this->binary);
            $this->exec->addCommand($rsync);

            if ($this->args) {
                $rsync->addOption($this->replaceTargetPlaceholder($this->args, $target));
            } else {
                // std err > dev null
                $rsync->silence();

                $targetFile = $target->getPathname();
                $targetDir = dirname($targetFile);

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
                // get rsync host string
                $syncTarget = $this->getRsyncHostString();
                // remote path
                $syncTarget .= $this->path;

                $rsync->addArgument($syncTarget);
            }
        }
        return $this->exec;
    }

    /**
     * Return rsync host string.
     *
     * @return string
     */
    public function getRsyncHostString()
    {
        $host = '';
        // remote host
        if (null !== $this->host) {
            // remote user
            if (null !== $this->user) {
                $host .= $this->user . '@';
            }
            $host .= $this->host . ':';
        }
        return $host;
    }
}
