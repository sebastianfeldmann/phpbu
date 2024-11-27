<?php

namespace phpbu\App\Cli\Executable;

use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Influxdump executable class.
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
class Influxdump extends Abstraction
{
    use OptionMasker;

    /**
     * Host to connect to
     * -host <hostname>:<port>
     *
     * @var string
     */
    private $host;

    /**
     * Database to backup
     *
     * @var string
     */
    private $databaseToDump;

    /**
     * Path to dump file
     *
     * @var string
     */
    private $dumpPathname;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('influxd', $path);
    }

    /**
     * Set the influxdb hostname.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Influxdump
     */
    public function useHost(string $host) : Influxdump
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set database to dump.
     *
     * @param  string $database
     * @return \phpbu\App\Cli\Executable\Influxdump
     */
    public function dumpDatabases(string $database) : Influxdump
    {
        $this->databaseToDump = $database;
        return $this;
    }

    /**
     * Set the dump target path.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Influxdump
     */
    public function dumpTo(string $path) : Influxdump
    {
        $this->dumpPathname = $path;
        return $this;
    }

    /**
     * Influxd CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOption('backup');
        $cmd->addOption('-portable');
        $cmd->addOptionIfNotEmpty('-host', $this->host);

        $this->configureSourceDatabases($cmd);
        $this->configureOutput($cmd);

        return $process;
    }

    /**
     * Configure source databases.
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureSourceDatabases(Cmd $cmd)
    {
        // different handling for different amounts of databases
        if ($this->databaseToDump !== "") {
            // single database use argument
            $cmd->addOptionIfNotEmpty('-database', $this->databaseToDump);
        }
    }

    /**
     * Configure output redirect.
     *
     * param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureOutput(Cmd $cmd)
    {
        // disable output redirection if files per table is active
        if ($this->dumpPathname === null) {
            $cmd->addOption("/tmp/influxdump");
        } else {
            $cmd->addOption($this->dumpPathname);
        }
    }
}
