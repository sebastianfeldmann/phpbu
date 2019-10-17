<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Backup\Target\Compression;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Util\Cli;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Mysqldump Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump extends Abstraction implements Executable
{
    use OptionMasker;

    /**
     * Host to connect to
     * --host <hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * --port <number>
     *
     * @var int
     */
    private $port;

    /**
     * The connection protocol
     * --protocol
     *
     * @var string
     */
    private $protocol;

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
     * List of tables to backup
     * --tables array of strings
     *
     * @var array
     */
    private $tablesToDump = [];

    /**
     * List of databases to backup
     * --databases array of strings
     *
     * @var array
     */
    private $databasesToDump = [];

    /**
     * List of tables to ignore
     *
     * @var array
     */
    private $tablesToIgnore = [];

    /**
     * List of tables where only the table structure is stored
     *
     * @var array
     */
    private $structureOnly = [];

    /**
     * Use mysqldump quick mode
     * -q
     *
     * @var bool
     */
    private $quick = false;

    /**
     * Lock tables option
     * --lock-tables
     *
     * @var bool
     */
    private $lockTables;

    /**
     * Issue a BEGIN SQL statement before dumping data from server
     * --single-transaction
     *
     * @var bool
     */
    private $singleTransaction;

    /**
     * Use mysqldump with compression
     * -C
     *
     * @var bool
     */
    private $compress = false;

    /**
     * Dump only table structures
     * --no-data
     *
     * @var bool
     */
    private $noData = false;

    /**
     * Whether to add SET @@GLOBAL.GTID_PURGED to output
     *
     * @var string
     */
    private $gtidPurged;

    /**
     * Table separated data files
     * --tab
     *
     * @var bool
     */
    private $filePerTable;

    /**
     * Skip mysqldump extended insert mode
     * --skip-extended-insert
     *
     * @var bool
     */
    private $skipExtendedInsert = false;

    /**
     * Dump blob fields as hex.
     * --hex-blob
     *
     * @var bool
     */
    private $hexBlob = false;

    /**
     * Dump routines.
     * --routines
     *
     * @var bool
     */
    private $routines = false;

    /**
     * Skip triggers
     * --skip-triggers
     *
     * @var boolean
     */
    private $skipTriggers = false;

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
     * Constructor
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('mysqldump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the mysql credentials
     *
     * @param  string $user
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function credentials(string $user = '', string $password = '') : Mysqldump
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the mysql hostname
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useHost(string $host) : Mysqldump
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the mysql port
     *
     * @param  int $port
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function usePort(int $port) : Mysqldump
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set the connection protocol.
     *
     * @param  string $protocol
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useProtocol(string $protocol) : Mysqldump
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Use '-q' quick mode
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useQuickMode(bool $bool) : Mysqldump
    {
        $this->quick = $bool;
        return $this;
    }

    /**
     * Use '--lock-tables' option
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function lockTables(bool $bool) : Mysqldump
    {
        $this->lockTables = $bool;
        return $this;
    }

    /**
     * Use '--single-transaction' option
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function singleTransaction(bool $bool) : Mysqldump
    {
        $this->singleTransaction = $bool;
        return $this;
    }

    /**
     * Use '-C' compress mode
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useCompression(bool $bool) : Mysqldump
    {
        $this->compress = $bool;
        return $this;
    }

    /**
     * Use '--skip-extended-insert' option
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function skipExtendedInsert(bool $bool) : Mysqldump
    {
        $this->skipExtendedInsert = $bool;
        return $this;
    }

    /**
     * Use '--hex-blob' to encode binary fields
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpBlobsHexadecimal(bool $bool) : Mysqldump
    {
        $this->hexBlob = $bool;
        return $this;
    }

    /**
     * Set tables to dump
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpTables(array $tables) : Mysqldump
    {
        $this->tablesToDump = $tables;
        return $this;
    }

    /**
     * Set databases to dump
     *
     * @param  array $databases
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpDatabases(array $databases) : Mysqldump
    {
        $this->databasesToDump = $databases;
        return $this;
    }

    /**
     * Set tables to ignore
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function ignoreTables(array $tables) : Mysqldump
    {
        $this->tablesToIgnore = $tables;
        return $this;
    }

    /**
     * Set tables where only table structure should be dumped
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpStructureOnly(array $tables) : Mysqldump
    {
        $this->structureOnly = $tables;
        return $this;
    }

    /**
     * Dump no table data at all
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpNoData(bool $bool) : Mysqldump
    {
        $this->noData = $bool;
        return $this;
    }

    /**
     * Add a general transaction ID statement to the dump file
     *
     * @param  string $purge
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function addGTIDStatement(string $purge)
    {
        $this->gtidPurged = in_array($purge, ['ON', 'OFF', 'AUTO']) ? strtoupper($purge) : '';
        return $this;
    }

    /**
     * Produce table separated data files
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function produceFilePerTable(bool $bool) : Mysqldump
    {
        $this->filePerTable = $bool;
        return $this;
    }

    /**
     * Dump procedures and functions
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpRoutines(bool $bool) : Mysqldump
    {
        $this->routines = $bool;
        return $this;
    }

    /**
     * Skip triggers
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function skipTriggers(bool $bool) : Mysqldump
    {
        $this->skipTriggers = $bool;
        return $this;
    }

    /**
     * Pipe compressor
     *
     * @param  \phpbu\App\Backup\Target\Compression $compression
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function compressOutput(Compression $compression) : Mysqldump
    {
        $this->compression = $compression;
        return $this;
    }

    /**
     * Set the dump target path
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpTo(string $path) : Mysqldump
    {
        $this->dumpPathname = $path;
        return $this;
    }

    /**
     * Mysqldump CommandLine generator
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('--user', $this->user);
        $cmd->addOptionIfNotEmpty('--password', $this->password);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--port', $this->port);
        $cmd->addOptionIfNotEmpty('--protocol', $this->protocol);
        $cmd->addOptionIfNotEmpty('--lock-tables', $this->lockTables, false);
        $cmd->addOptionIfNotEmpty('--single-transaction', $this->singleTransaction, false);
        $cmd->addOptionIfNotEmpty('-q', $this->quick, false);
        $cmd->addOptionIfNotEmpty('-C', $this->compress, false);
        $cmd->addOptionIfNotEmpty('--skip-extended-insert', $this->skipExtendedInsert, false);
        $cmd->addOptionIfNotEmpty('--hex-blob', $this->hexBlob, false);
        $cmd->addOptionIfNotEmpty('--set-gtid-purged', $this->gtidPurged);
        $cmd->addOptionIfNotEmpty('--routines', $this->routines, false);
        $cmd->addOptionIfNotEmpty('--skip-triggers', $this->skipTriggers, false);

        $this->configureSourceData($cmd);
        $this->configureIgnoredTables($cmd);

        if ($this->filePerTable) {
            $cmd->addOption('--tab', $this->dumpPathname);
        }

        if ($this->noData) {
            $cmd->addOption('--no-data');
        } else {
            if (count($this->structureOnly)) {
                $cmd2 = clone($cmd);
                foreach ($this->structureOnly as $table) {
                    $cmd2->addOption('--ignore-table', $table);
                }
                $cmd2->addOption('--skip-add-drop-table');
                $cmd2->addOption('--no-create-db');
                $cmd2->addOption('--no-create-info');
                $cmd->addOption('--no-data');
                $process->addCommand($cmd2);
            }
        }
        $this->configureCompression($process);
        $this->configureOutput($process);
        return $process;
    }

    /**
     * Configure source data (tables, databases)
     *
     * @param  \SebastianFeldmann\Cli\Command\Executable $cmd
     * @throws \phpbu\App\Exception
     */
    private function configureSourceData(Cmd $cmd)
    {
        if (count($this->tablesToDump)) {
            $this->configureSourceTables($cmd);
        } else {
            $this->configureSourceDatabases($cmd);
        }
    }

    /**
     * Configure source tables
     *
     * @param  \SebastianFeldmann\Cli\Command\Executable $cmd
     * @throws \phpbu\App\Exception
     */
    private function configureSourceTables(Cmd $cmd)
    {
        if (count($this->databasesToDump) !== 1) {
            throw new Exception('mysqldump --tables needs exactly one database');
        }
        $cmd->addArgument($this->databasesToDump[0]);
        $cmd->addOption('--tables', $this->tablesToDump);
    }

    /**
     * Configure source databases
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureSourceDatabases(Cmd $cmd)
    {
        $databasesToDump = count($this->databasesToDump);

        // different handling for different amounts of databases
        if ($databasesToDump == 1) {
            // single database use argument
            $cmd->addArgument($this->databasesToDump[0]);
        } elseif ($databasesToDump > 1) {
            // multiple databases add list with --databases
            $cmd->addOption('--databases', $this->databasesToDump);
        } else {
            // no databases set dump all databases
            $cmd->addOption('--all-databases');
        }
    }

    /**
     * Add --ignore-table options
     *
     * @param \SebastianFeldmann\Cli\Command\Executable $cmd
     */
    private function configureIgnoredTables(Cmd $cmd)
    {
        if (count($this->tablesToIgnore)) {
            foreach ($this->tablesToIgnore as $table) {
                $cmd->addOption('--ignore-table', $table);
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
        if (!$this->filePerTable && !empty($this->compression)) {
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
        // disable output redirection if files per table is active
        if (!$this->filePerTable) {
            $process->redirectOutputTo(
                $this->dumpPathname . (!empty($this->compression) ? '.' . $this->compression->getSuffix() : '')
            );
        }
    }
}
