<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

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
class Rsync extends Abstraction
{
    use OptionMasker;

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
     * Password to use to authenticate
     *
     * @var string
     */
    private $password;

    /**
     * Path to password file
     *
     * @var string
     */
    private $passwordFile;

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
    protected $excludes = [];

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
    public function __construct(string $path = '')
    {
        $this->setup('rsync', $path);
        $this->setMaskCandidates(['password']);
        $this->source = new Rsync\Location();
        $this->target = new Rsync\Location();
    }

    /**
     * Set custom args
     *
     * @param  string $args
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function useArgs(string $args) : Rsync
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Set password environment variable RSYNC_PASSWORD.
     *
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function usePassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set path to password file.
     *
     * @param  string $file
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function usePasswordFile(string $file)
    {
        $this->passwordFile = $file;
        return $this;
    }


    /**
     * Set source user.
     *
     * @param  string $user
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function fromUser(string $user) : Rsync
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
    public function fromHost(string $host) : Rsync
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
    public function fromPath(string $path) : Rsync
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
    public function compressed(bool $bool) : Rsync
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
    public function toHost(string $host) : Rsync
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
    public function toPath(string $path) : Rsync
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
    public function toUser(string $user) : Rsync
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
    public function exclude(array $excludes) : Rsync
    {
        $this->excludes = $excludes;
        return $this;
    }

    /**
     * Use --delete option.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Rsync
     */
    public function removeDeleted(bool $bool) : Rsync
    {
        $this->delete = $bool;
        return $this;
    }

    /**
     * Rsync CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $password = !empty($this->password) ? 'RSYNC_PASSWORD=' . escapeshellarg($this->password) . ' ' : '';
        $process  = new CommandLine();
        $cmd      = new Cmd($password . $this->binary);
        $process->addCommand($cmd);

        if (!empty($this->args)) {
            $cmd->addOption($this->args);
        } else {
            // make sure source and target are valid
            $this->validateLocations();

            // use archive mode, verbose and compress if not already done
            $options = '-av' . ($this->compressed ? 'z' : '');
            $cmd->addOption($options);
            $this->configureExcludes($cmd, $this->excludes);
            $cmd->addOptionIfNotEmpty('--delete', $this->delete, false);
            $cmd->addOptionIfNotEmpty('--password-file', $this->passwordFile);
            $cmd->addArgument($this->source->toString());
            $cmd->addArgument($this->target->toString());
        }

        return $process;
    }

    /**
     * Makes sure source and target are valid.
     *
     * @throws \phpbu\App\Exception
     */
    protected function validateLocations()
    {
        if (!$this->source->isValid()) {
            throw new Exception('source path is missing');
        }
        if (!$this->target->isValid()) {
            throw new Exception('target path is missing');
        }
    }

    /**
     * Configure excludes.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     * @param array                                     $excludes
     */
    protected function configureExcludes(Cmd $cmd, array $excludes)
    {
        foreach ($excludes as $ex) {
            $cmd->addOption('--exclude', $ex);
        }
    }
}
