<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Xtrabackup8 Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.10
 */
class Xtrabackup8 extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * MySQL data directory
     *
     * @var string
     */
    private $dataDir;

    /**
     * Dump directory
     *
     * @var string
     */
    private $dumpDir;

    /**
     * Host to connect to
     * --host <hostname>
     *
     * @var string
     */
    private $host;

    /**
     * User to connect with
     * --user <username>
     *
     * @var string
     */
    private $user;

    /**
     * Password to authenticate with
     * --password <password>
     *
     * @var string
     */
    private $password;

    /**
     * Regular expression matching the tables to be backed up.
     * The regex should match the full qualified name: myDatabase.myTable
     * --tables string
     *
     * @var string
     */
    private $include;

    /**
     * List of databases and/or tables to backup
     * Tables must e fully qualified: myDatabase.myTable
     * --databases array of strings
     *
     * @var array
     */
    private $databases = [];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('xtrabackup', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set MySQL data dir.
     *
     * @param  string $path
     * @return Xtrabackup8
     */
    public function dumpFrom(string $path) : Xtrabackup8
    {
        $this->dataDir = $path;
        return $this;
    }

    /**
     * Set target dump dir.
     *
     * @param  string $path
     * @return Xtrabackup8
     */
    public function dumpTo(string $path) : Xtrabackup8
    {
        $this->dumpDir = $path;
        return $this;
    }

    /**
     * Set host du connect to.
     *
     * @param  string $host
     * @return Xtrabackup8
     */
    public function useHost(string $host) : Xtrabackup8
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set mysql credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return Xtrabackup8
     */
    public function credentials(string $user = '', string $password = '') : Xtrabackup8
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set databases to dump.
     *
     * @param  array $databases
     * @return Xtrabackup8
     */
    public function dumpDatabases(array $databases)
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * Xtrabackup8 CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws Exception
     */
    public function createCommandLine() : CommandLine
    {
        if (empty($this->dumpDir)) {
            throw new Exception('no directory to dump to');
        }
        $process  = new CommandLine();
        $cmdDump  = new Cmd($this->binary);
        $process->addCommand($cmdDump);

        $cmdDump->addOption('--backup');
        $cmdDump->addOptionIfNotEmpty('--datadir', $this->dataDir);
        $cmdDump->addOptionIfNotEmpty('--user', $this->user);
        $cmdDump->addOptionIfNotEmpty('--password', $this->password);
        $cmdDump->addOptionIfNotEmpty('--host', $this->host);

        if (count($this->databases)) {
            $cmdDump->addOption('--databases', implode(' ', $this->databases));
        }

        $cmdDump->addOption('--target-dir', $this->dumpDir);

        return $process;
    }
}
