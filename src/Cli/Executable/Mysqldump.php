<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Backup\Target\Compression;
use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;
use phpbu\App\Util\Cli;

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
     * @var boolean
     */
    private $noData = false;

    /**
     * Table separated data files
     * --tab
     *
     * @var bool
     */
    private $filePerTable;

    /**
     * Use mysqldump extended insert mode
     * -e, --extended-insert
     *
     * @var boolean
     */
    private $extendedInsert = false;

    /**
     * Dump blob fields as hex.
     * --hex-blob
     *
     * @var boolean
     */
    private $hexBlob = false;

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
    public function __construct($path = null)
    {
        $this->setup('mysqldump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the mysql credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function credentials($user = null, $password = null)
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the mysql hostname.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Use '-q' quick mode.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useQuickMode($bool)
    {
        $this->quick = $bool;
        return $this;
    }

    /**
     * Use '--lock-tables' option.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function lockTables($bool)
    {
        $this->lockTables = $bool;
        return $this;
    }

    /**
     * Use '--single-transaction' option.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function singleTransaction($bool)
    {
        $this->singleTransaction = $bool;
        return $this;
    }

    /**
     * Use '-C' compress mode.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useCompression($bool)
    {
        $this->compress = $bool;
        return $this;
    }

    /**
     * Use '-e' extended insert mode.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function useExtendedInsert($bool)
    {
        $this->extendedInsert = $bool;
        return $this;
    }

    /**
     * Use '--hex-blob' to encode binary fields.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpBlobsHexadecimal($bool)
    {
        $this->hexBlob = $bool;
        return $this;
    }

    /**
     * Set tables to dump.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpTables(array $tables)
    {
        $this->tablesToDump = $tables;
        return $this;
    }

    /**
     * Set databases to dump.
     *
     * @param  array $databases
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpDatabases(array $databases)
    {
        $this->databasesToDump = $databases;
        return $this;
    }

    /**
     * Set tables to ignore.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function ignoreTables(array $tables)
    {
        $this->tablesToIgnore = $tables;
        return $this;
    }

    /**
     * Set tables where only table structure should be dumped.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpStructureOnly(array $tables)
    {
        $this->structureOnly = $tables;
        return $this;
    }

    /**
     * Dump no table data at all.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpNoData($bool)
    {
        $this->noData = $bool;
        return $this;
    }

    /**
     * Produce table separated data files.
     *
     * @param  bool $bool
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function produceFilePerTable($bool)
    {
        $this->filePerTable = $bool;
        return $this;
    }

    /**
     * Pipe compressor.
     *
     * @param  \phpbu\App\Backup\Target\Compression $compression
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function compressOutput(Compression $compression)
    {
        $this->compression = $compression;
        return $this;
    }

    /**
     * Set the dump target path.
     *
     * @param  string $path
     * @return \phpbu\App\Cli\Executable\Mysqldump
     */
    public function dumpTo($path)
    {
        $this->dumpPathname = $path;
        return $this;
    }

    /**
     * Process generator
     */
    protected function createProcess()
    {
        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        $cmd->addOptionIfNotEmpty('--user', $this->user);
        $cmd->addOptionIfNotEmpty('--password', $this->password);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--lock-tables', $this->lockTables, false);
        $cmd->addOptionIfNotEmpty('--single-transaction', $this->singleTransaction, false);
        $cmd->addOptionIfNotEmpty('-q', $this->quick, false);
        $cmd->addOptionIfNotEmpty('-C', $this->compress, false);
        $cmd->addOptionIfNotEmpty('-e', $this->extendedInsert, false);
        $cmd->addOptionIfNotEmpty('--hex-blob', $this->hexBlob, false);

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
     * Configure source data (tables, databases).
     *
     * @param  \phpbu\App\Cli\Cmd $cmd
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
     * Configure source tables.
     *
     * @param  \phpbu\App\Cli\Cmd $cmd
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
     * Configure source databases.
     *
     * @param  \phpbu\App\Cli\Cmd $cmd
     */
    private function configureSourceDatabases(Cmd $cmd)
    {
        $databasesToDump = count($this->databasesToDump);

        // different handling for different ammounts of databases
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
     * @param \phpbu\App\Cli\Cmd $cmd
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
     * Add compressor pipe if set.
     *
     * @param \phpbu\App\Cli\Process $process
     */
    private function configureCompression(Process $process)
    {
        // if file per table isn't active and a compressor is set
        if (!$this->filePerTable && !empty($this->compression)) {
            $binary = Cli::detectCmdLocation($this->compression->getCommand(), $this->compression->getPath());
            $cmd    = new Cmd($binary);
            $process->pipeOutputTo($cmd);
        }
    }

    /**
     * Configure output redirect.
     *
     * @param \phpbu\App\Cli\Process $process
     */
    private function configureOutput(Process $process)
    {
        // disable output redirection if files per table is active
        if (!$this->filePerTable) {
            $process->redirectOutputTo(
                $this->dumpPathname . (!empty($this->compression) ? '.' . $this->compression->getSuffix() : '')
            );
        }
    }
}
