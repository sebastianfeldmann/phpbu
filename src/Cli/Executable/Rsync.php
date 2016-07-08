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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Rsync extends Abstraction implements Executable
{
    /**
     * Source
     *
     * @var \phpbu\App\Cli\Executable\Rsync\Location
     */
    private $source;

    /**
     * Target
     *
     * @var \phpbu\App\Cli\Executable\Rsync\Location
     */
    private $target;

    /**
     * Raw args
     *
     * @var string
     */
    protected $args;

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
        $this->setup('rsync', $path);
        $this->source = new Rsync\Location();
        $this->target = new Rsync\Location();
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
     * Set source user.
     *
     * @param  string $user
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function fromUser($user)
    {
        $this->source->setUser($user);
        return $this;
    }

    /**
     * Set source host.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function fromHost($host)
    {
        $this->source->setHost($host);
        return $this;
    }

    /**
     * Set source path.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function fromPath($path)
    {
        $this->source->setPath($path);
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
        $this->target->setHost($host);
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
        $this->target->setPath($path);
        return $this;
    }

    /**
     * Set user to connect as.
     *
     * @param  string $user
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function toUser($user)
    {
        $this->target->setUser($user);
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
            if (!$this->source->isValid()) {
                throw new Exception('source path is missing');
            }
            if (!$this->target->isValid()) {
                throw new Exception('target path is missing');
            }

            // use archive mode, verbose and compress if not already done
            $options = '-av' . ($this->compressed ? 'z' : '');
            $cmd->addOption($options);

            if (count($this->excludes)) {
                foreach ($this->excludes as $ex) {
                    $cmd->addOption('--exclude', $ex);
                }
            }

            $cmd->addOptionIfNotEmpty('--delete', $this->delete, false);

            $cmd->addArgument($this->source->toString());
            $cmd->addArgument($this->target->toString());
        }

        return $process;
    }
}
