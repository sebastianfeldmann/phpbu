<?php

namespace phpbu\App\Cli\Executable;

use phpbu\App\Backup\Target\Compression;
use phpbu\App\Cli\Executable;
use phpbu\App\Util\Cli;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Ldapdump executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Julian Marié <julian.marie@free.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class Ldapdump extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Host to connect to
     * -h <hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * -p <number>
     *
     * @var int
     */
    private $port;

    /**
     * Basename
     * -b <basename>
     *
     * @var string
     */
    private $searchBase;

    /**
     * BindDn to connect with
     * -D <DN>
     *
     * @var string
     */
    private $bindDn;

    /**
     * Password to authenticate with
     * -w <password>
     *
     * @var string
     */
    private $password;

    /**
     * Filter
     * <filter>
     *
     * @var string
     */
    private $filter;

    /**
     * Attributes
     * <attrs>
     *
     * @var array
     */
    private $attrs;

    /**
     * Path to dump file
     *
     * @var string
     */
    private $dumpPathname;

    /**
     * Compression command to pipe output to
     *
     * @var \phpbu\App\Backup\Target\Compression
     */
    private $compression;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('ldapsearch', $path);
    }

    /**
     * Set the ldap credentials
     *
     * @param  string $bindDn
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function credentials(string $bindDn = '', string $password = '') : Ldapdump
    {
        $this->bindDn   = $bindDn;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the ldapdb hostname.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function useHost(string $host) : Ldapdump
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the ldap port
     *
     * @param  int $port
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function usePort(int $port) : Ldapdump
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set the ldap searchBase
     *
     * @param  string $searchBase
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function useSearchBase(string $searchBase) : Ldapdump
    {
        $this->searchBase = $searchBase;
        return $this;
    }

    /**
     * Set the ldap filter
     *
     * @param  string $filter
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function useFilter(string $filter) : Ldapdump
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set the ldap attrs
     *
     * @param  array $attrs
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function useAttributes(array $attrs) : Ldapdump
    {
        $this->attrs = $attrs;
        return $this;
    }

    /**
     * Pipe compressor
     *
     * @param  \phpbu\App\Backup\Target\Compression $compression
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function compressOutput(Compression $compression) : Ldapdump
    {
        $this->compression = $compression;
        return $this;
    }

    /**
     * Set the dump target path
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Ldapdump
     */
    public function dumpTo(string $path) : Ldapdump
    {
        $this->dumpPathname = $path;
        return $this;
    }

    /**
     * Ldapd CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('-b', $this->searchBase, true, ' ');
        $cmd->addOption('-x');
        $cmd->addOptionIfNotEmpty('-h', $this->host, true, ' ');
        $cmd->addOptionIfNotEmpty('-p', $this->port, true, ' ');
        $cmd->addOptionIfNotEmpty('-D', $this->bindDn, true, ' ');
        $cmd->addOptionIfNotEmpty('-w', $this->password, true, ' ');

        $this->configureFilter($cmd);
        $this->configureAttrs($cmd);
        $this->configureCompression($process);
        $this->configureOutput($process);

        return $process;
    }

    /**
     * Configure Filter
     *
     * param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureFilter(Cmd $cmd)
    {
        $cmd->addOption("'{$this->filter}'");
    }

    /**
     * Configure Attributes
     *
     * param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureAttrs(Cmd $cmd)
    {
        if ($this->attrs) {
            foreach ($this->attrs as $attr) {
                $cmd->addOption("'$attr'");
            }
        }
    }

    /**
     * Add compressor pipe if set
     *
     * @param \SebastianFeldmann\Cli\CommandLine $process
     */
    private function configureCompression(CommandLine $process)
    {
        // if file per table isn't active and a compressor is set
        if (!empty($this->compression)) {
            $binary = Cli::detectCmdLocation($this->compression->getCommand(), $this->compression->getPath());
            $cmd    = new Cmd($binary);
            $process->pipeOutputTo($cmd);
        }
    }

    /**
     * Configure output redirect
     *
     * @param \SebastianFeldmann\Cli\CommandLine $process
     */
    private function configureOutput(CommandLine $process)
    {
        $process->redirectOutputTo(
            $this->dumpPathname . (!empty($this->compression) ? '.' . $this->compression->getSuffix() : '')
        );
    }
}
