<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Exception;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Pgdump Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Pgdump extends Abstraction
{
    use OptionMasker;

    /**
     * Host to connect to
     * --host=<hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * --port=<portnumber>
     *
     * @var int
     */
    private $port;

    /**
    * Run the dump in parallel by dumping njobs tables simultaneously.
    * --jobs=njobs
    * @var int
    */
    private $jobs;

    /**
     * Set SSL mode
     * PGSSLMODE=allow pg_dump ...
     *
     * @var string
     */
    private $sslMode;

    /**
     * User to connect with
     * --user=<username>
     *
     * @var string
     */
    private $user;

    /**
     * Password to authenticate with
     * --password=<password>
     *
     * @var string
     */
    private $password;

    /**
     * Database to dump
     * db-name
     *
     * @var string
     */
    private $databaseToDump;

    /**
     * List of schmeas to dump
     * --schema=<schema>
     *
     * @var array
     */
    private $schemasToDump = [];

    /**
     * Exclude Schemas
     * --exclude-schema=<schema>
     *
     * @var array
     */
    private $schemasToExclude = [];

    /**
     * Tables to dump.
     * --table=<table>
     *
     * @var array
     */
    private $tablesToDump = [];

    /**
     * List of tables to exclude
     * --exclude-table=<table>
     *
     * @var array
     */
    private $tablesToExclude = [];

    /**
     * Don't dump the structure
     * --data-only
     *
     * @var boolean
     */
    private $dataOnly;

    /**
     * Dump only schema definitions.
     * --schema-only
     *
     * @var boolean
     */
    private $schemaOnly;

    /**
     * Do not dump data for any tables matching the table pattern.
     * --exclude-table-data
     *
     * @var array
     */
    private $excludeTableData = [];

    /**
     * Add drop statements to the dump.
     * --clean
     *
     * @var boolean
     */
    private $clean = false;

    /**
     * Encoding of the dump file
     * --encoding
     *
     * @var string
     */
    private $encoding;

    /**
     * postgreSQL dump format definition
     * --format [plain|custom|directory|tar]
     *
     * @var string
     */
    private $format;

    /**
     * Allow any user to restore the dump
     * --no-owner
     *
     * @var bool
     */
    private $noOwner = false;

    /**
     * Prevent dumping of access privileges.
     * --no-acl
     *
     * @var boolean
     */
    private $noPrivileges;

    /**
     * Do not output commands to select tablespaces.
     * --no-tablespaces
     *
     * @var boolean
     */
    private $noTablespaces;

    /**
     * File to dump to
     * --file
     *
     * @var string
     */
    private $file;

    /**
     * List of available output formats
     *
     * @var array
     */
    private $availableFormats = [
        'p'         => true,
        'plain'     => true,
        'c'         => true,
        'custom'    => true,
        'd'         => true,
        'directory' => true,
        't'         => true,
        'tar'       => true,
    ];

    /**
     * List of available sslmode
     *
     * @var array
     */
    private $availableSslMode = [
        'disable' => true,
        'allow' => true,
        'prefer' => true,
        'require' => true,
        'verify-ca' => true,
        'verify-full' => true,
    ];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->setup('pg_dump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the postgreSQL credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return Pgdump
     */
    public function credentials(string $user = '', string $password = '') : Pgdump
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the postgreSQL hostname.
     *
     * @param  string $host
     * @return Pgdump
     */
    public function useHost(string $host) : Pgdump
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the postgreSQL port.
     *
     * @param  int $port
     * @return Pgdump
     */
    public function usePort(int $port) : Pgdump
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Define njobs tables simultaneously..
     *
     * @param  int $jobs
     * @return Pgdump
     */
    public function dumpJobs(int $jobs): Pgdump
    {
        if ($jobs < 0) {
            throw new Exception('invalid jobs value');
        }
        $this->jobs = $jobs;
        return $this;
    }

    /**
     * Set the sslmode
     *
     * @param string $sslMode
     * @return Pgdump
     */
    public function sslMode(string $sslMode): Pgdump
    {
        if ($sslMode && !isset($this->availableSslMode[$sslMode])) {
            throw new Exception('invalid sslMode');
        }
        $this->sslMode = $sslMode;
        return $this;
    }

    /**
     * Set database to dump.
     *
     * @param  string $database
     * @return Pgdump
     */
    public function dumpDatabase(string $database) : Pgdump
    {
        $this->databaseToDump = $database;
        return $this;
    }

    /**
     * Add drop statements to the dump file.
     * Works only on format=plain-text.
     *
     * @param  boolean $bool
     * @return Pgdump
     */
    public function addDropStatements(bool $bool) : Pgdump
    {
        $this->clean = $bool;
        return $this;
    }

    /**
     * Add the --no-owner option so no ownership setting commands will be added.
     *
     * @param  boolean $bool
     * @return Pgdump
     */
    public function skipOwnerCommands(bool $bool) : Pgdump
    {
        $this->noOwner = $bool;
        return $this;
    }

    /**
     * Set schemas to dump.
     *
     * @param  array $schemas
     * @return Pgdump
     */
    public function dumpSchemas(array $schemas) : Pgdump
    {
        $this->schemasToDump = $schemas;
        return $this;
    }

    /**
     * Set schemas to exclude.
     *
     * @param  array $schemas
     * @return Pgdump
     */
    public function excludeSchemas(array $schemas) : Pgdump
    {
        $this->schemasToExclude = $schemas;
        return $this;
    }

    /**
     * Set tables to dump.
     *
     * @param  array $tables
     * @return Pgdump
     */
    public function dumpTables(array $tables) : Pgdump
    {
        $this->tablesToDump = $tables;
        return $this;
    }

    /**
     * Set tables to ignore.
     *
     * @param  array $tables
     * @return Pgdump
     */
    public function excludeTables(array $tables) : Pgdump
    {
        $this->tablesToExclude = $tables;
        return $this;
    }

    /**
     * Set tables where no data is dumped.
     *
     * @param  array $tables
     * @return Pgdump
     */
    public function excludeTableData(array $tables) : Pgdump
    {
        $this->excludeTableData = $tables;
        return $this;
    }

    /**
     * Dump only the schema information.
     *
     * @param  boolean $bool
     * @return Pgdump
     * @throws Exception
     */
    public function dumpSchemaOnly(bool $bool) : Pgdump
    {
        if ($this->dataOnly) {
            throw new Exception('can\'t use schema-only when data-only is used already');
        }
        $this->schemaOnly = $bool;
        return $this;
    }

    /**
     * Dump no schema information.
     *
     * @param  boolean $bool
     * @return Pgdump
     * @throws Exception
     */
    public function dumpDataOnly(bool $bool) : Pgdump
    {
        if ($this->schemaOnly) {
            throw new Exception('can\'t use data-only when schema-only is used already');
        }
        $this->dataOnly = $bool;
        return $this;
    }

    /**
     * Set the dump target path.
     *
     * @param  string $path
     * @return Pgdump
     */
    public function dumpTo(string $path) : Pgdump
    {
        $this->file = $path;
        return $this;
    }

    /**
     * Set the dump format.
     *
     * @param  string $format
     * @return Pgdump
     * @throws Exception
     */
    public function dumpFormat(string $format) : Pgdump
    {
        if (!isset($this->availableFormats[$format])) {
            throw new Exception('invalid format');
        }
        $this->format = $format;
        return $this;
    }

    /**
     * Do not dump commands setting ownership.
     * --no-owner
     *
     * @param  bool $bool
     * @return Pgdump
     */
    public function dumpNoOwner(bool $bool) : Pgdump
    {
        $this->noOwner = $bool;
        return $this;
    }

    /**
     * Do not output commands to select tablespaces.
     * --no-tablespaces
     *
     * @param  bool $bool
     * @return Pgdump
     */
    public function dumpNoTablespaces(bool $bool) : Pgdump
    {
        $this->noTablespaces = $bool;
        return $this;
    }

    /**
     * Prevent dumping of access privileges.
     * --no-acl
     *
     * @param  boolean $bool
     * @return Pgdump
     */
    public function dumpNoPrivileges(bool $bool) : Pgdump
    {
        $this->noPrivileges = $bool;
        return $this;
    }
    /**
     * Set the output file encoding.
     *
     * @param  string $encoding
     * @return Pgdump
     */
    public function encode(string $encoding) : Pgdump
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Pgdump CommandLine generator.
     *
     * @return CommandLine
     */
    protected function createCommandLine() : CommandLine
    {
        $process  = new CommandLine();
        $cmd      = new Cmd($this->binary);

        $this->handleVariables($cmd);

        $process->addCommand($cmd);

        // always disable password prompt
        $cmd->addOption('-w');

        $cmd->addOptionIfNotEmpty('--username', $this->user);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--port', $this->port);
        $cmd->addOptionIfNotEmpty('--jobs', $this->jobs);
        $cmd->addOptionIfNotEmpty('--dbname', $this->databaseToDump);
        $cmd->addOptionIfNotEmpty('--schema-only', $this->schemaOnly, false);
        $cmd->addOptionIfNotEmpty('--data-only', $this->dataOnly, false);
        $cmd->addOptionIfNotEmpty('--clean', $this->clean, false);
        $cmd->addOptionIfNotEmpty('--no-owner', $this->noOwner, false);
        $cmd->addOptionIfNotEmpty('--encoding', $this->encoding);
        $cmd->addOptionIfNotEmpty('--no-tablespaces', $this->noTablespaces, false);
        $cmd->addOptionIfNotEmpty('--no-acl', $this->noPrivileges, false);


        $this->handleSchemas($cmd);
        $this->handleTables($cmd);

        $cmd->addOptionIfNotEmpty('--file', $this->file);
        $cmd->addOptionIfNotEmpty('--format', $this->format);
        return $process;
    }

    /**
     * This handles all command variables e.g SSLMODE or PASSWORD
     *
     * @param Cmd $cmd
     */
    private function handleVariables(Cmd $cmd): void
    {
        if ($this->password) {
            $cmd->addVar('PGPASSWORD', $this->password);
        }
        if ($this->sslMode) {
            $cmd->addVar('PGSSLMODE', $this->sslMode);
        }
    }

    /**
     * Handle command schema settings.
     *
     * @param Cmd $cmd
     */
    protected function handleSchemas(Cmd $cmd)
    {
        foreach ($this->schemasToDump as $schema) {
            $cmd->addOption('--schema', $schema);
        }

        foreach ($this->schemasToExclude as $table) {
            $cmd->addOption('--exclude-schema', $table);
        }
    }

    /**
     * Handle command table settings.
     *
     * @param Cmd $cmd
     */
    protected function handleTables(Cmd $cmd)
    {
        foreach ($this->tablesToDump as $table) {
            $cmd->addOption('--table', $table);
        }

        foreach ($this->tablesToExclude as $table) {
            $cmd->addOption('--exclude-table', $table);
        }

        foreach ($this->excludeTableData as $table) {
            $cmd->addOption('--exclude-table-data', $table);
        }
    }
}
