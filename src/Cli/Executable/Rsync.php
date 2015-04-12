<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

/**
 * Rsync executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.2
 */
class Rsync extends Abstraction implements Executable
{
    /**
     * Path to sync.
     *
     * @var string
     */
    private $syncSource;

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
     * Remove deleted files remotely as well
     *
     * @var boolean
     */
    protected $delete;

    /**
     * Compress data.
     *
     * @var boolean
     */
    protected $compressed = false;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->cmd = 'rsync';
        parent::__construct($path);
    }

    /**
     * Set custom args
     *
     * @param  string $args
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function useArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Set path to dump to.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function syncFrom($path)
    {
        $this->syncSource = $path;
        return $this;
    }

    /**
     * Use compression.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function compressed($bool)
    {
        $this->compressed = $bool;
        return $this;
    }

    /**
     * Sync to host.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function toHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set path to sync to.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function toPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set user to connect as.
     *
     * @param  string $user
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function asUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Exclude files.
     *
     * @param  array $excludes
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function exclude(array $excludes)
    {
        $this->excludes = $excludes;
        return $this;
    }

    /**
     * Use --delete option.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function removeDeleted($bool)
    {
        $this->delete = $bool;
        return $this;
    }

    /**
     * Subclass Process generator.
     *
     * @return \phpbu\App\Cli\Process
     * @throws \phpbu\App\Exception
     */
    protected function createProcess()
    {
        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        if (!empty($this->args)) {
            $cmd->addOption($this->args);
        } else {
            if (empty($this->syncSource)) {
                throw new Exception('source to sync is missing');
            }
            if (empty($this->path)) {
                throw new Exception('target path is missing');
            }
            // std err > dev null
            $cmd->silence();

            // use archive mode, verbose and compress if not already done
            $options = '-av' . ($this->compressed ? 'z' : '');
            $cmd->addOption($options);

            if (count($this->excludes)) {
                foreach ($this->excludes as $ex) {
                    $cmd->addOption('--exclude', $ex);
                }
            }

            $cmd->addOptionIfNotEmpty('--delete', $this->delete, false);
            $cmd->addArgument($this->syncSource);

            // target handling
            // get rsync host string
            $syncTarget = $this->getRsyncHostString();
            // remote path
            $syncTarget .= $this->path;

            $cmd->addArgument($syncTarget);
        }

        return $process;
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
