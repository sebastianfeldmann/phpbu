<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

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
class Pgdump extends Abstraction implements Executable
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
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->setup('pg_dump', $path);
        $this->setMaskCandidates(['password']);
    }

    /**
     * Set the postgreSQL credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function credentials($user = null, $password = null)
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the postgreSQL hostname.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function useHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the postgreSQL port.
     *
     * @param  int $port
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function usePort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set database to dump.
     *
     * @param  string $database
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpDatabase($database)
    {
        $this->databaseToDump = $database;
        return $this;
    }

    /**
     * Add drop statements to the dump file.
     * Works only on format=plain-text.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function addDropStatements($bool)
    {
        $this->clean = $bool;
        return $this;
    }

    /**
     * Add the --no-owner option so no ownership setting commands will be added.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function skipOwnerCommands($bool)
    {
        $this->noOwner = $bool;
        return $this;
    }

    /**
     * Set schemas to dump.
     *
     * @param  array $schemas
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpSchemas(array $schemas)
    {
        $this->schemasToDump = $schemas;
        return $this;
    }

    /**
     * Set schemas to exclude.
     *
     * @param  array $schemas
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function excludeSchemas(array $schemas)
    {
        $this->schemasToExclude = $schemas;
        return $this;
    }

    /**
     * Set tables to dump.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpTables(array $tables)
    {
        $this->tablesToDump = $tables;
        return $this;
    }

    /**
     * Set tables to ignore.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function excludeTables(array $tables)
    {
        $this->tablesToExclude = $tables;
        return $this;
    }

    /**
     * Set tables where no data is dumped.
     *
     * @param  array $tables
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function excludeTableData(array $tables)
    {
        $this->excludeTableData = $tables;
        return $this;
    }

    /**
     * Dump only the schema information.
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     * @throws \phpbu\App\Exception
     */
    public function dumpSchemaOnly($bool)
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
     * @return \phpbu\App\Cli\Executable\Pgdump
     * @throws \phpbu\App\Exception
     */
    public function dumpDataOnly($bool)
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
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpTo($path)
    {
        $this->file = $path;
        return $this;
    }

    /**
     * Set the dump format.
     *
     * @param  string $format
     * @return \phpbu\App\Cli\Executable\Pgdump
     * @throws \phpbu\App\Exception
     */
    public function dumpFormat($format)
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
     * @param $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpNoOwner($bool)
    {
        $this->noOwner = $bool;
        return $this;
    }

    /**
     * Do not output commands to select tablespaces.
     * --no-tablespaces
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpNoTablespaces($bool)
    {
        $this->noTablespaces = $bool;
        return $this;
    }

    /**
     * Prevent dumping of access privileges.
     * --no-acl
     *
     * @param  boolean $bool
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function dumpNoPrivileges($bool)
    {
        $this->noPrivileges = $bool;
        return $this;
    }
    /**
     * Set the output file encoding.
     *
     * @param  string $encoding
     * @return \phpbu\App\Cli\Executable\Pgdump
     */
    public function encode($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Process generator.
     *
     * @return \phpbu\App\Cli\Process
     */
    protected function createProcess()
    {
        $process  = new Process();
        $password = $this->password ? 'PGPASSWORD=' . escapeshellarg($this->password) . ' ' : '';
        $cmd      = new Cmd($password . $this->binary);
        $process->addCommand($cmd);

        // always disable password prompt
        $cmd->addOption('-w');

        $cmd->addOptionIfNotEmpty('--username', $this->user);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('--port', $this->port);
        $cmd->addOptionIfNotEmpty('--dbname', $this->databaseToDump);
        $cmd->addOptionIfNotEmpty('--schema-only', $this->schemaOnly, false);
        $cmd->addOptionIfNotEmpty('--data-only', $this->dataOnly, false);
        $cmd->addOptionIfNotEmpty('--clean', $this->clean, false);
        $cmd->addOptionIfNotEmpty('--no-owner', $this->noOwner, false);
        $cmd->addOptionIfNotEmpty('--encoding', $this->encoding);
        $cmd->addOptionIfNotEmpty('--no-tablespaces', $this->noTablespaces, false);
        $cmd->addOptionIfNotEmpty('--no-acl', $this->noPrivileges, false);

        foreach ($this->schemasToDump as $schema) {
            $cmd->addOption('--schema', $schema);
        }

        foreach ($this->schemasToExclude as $table) {
            $cmd->addOption('--exclude-schema', $table);
        }

        foreach ($this->tablesToDump as $table) {
            $cmd->addOption('--table', $table);
        }

        foreach ($this->tablesToExclude as $table) {
            $cmd->addOption('--exclude-table', $table);
        }

        foreach ($this->excludeTableData as $table) {
            $cmd->addOption('--exclude-table-data', $table);
        }

        $cmd->addOptionIfNotEmpty('--file', $this->file);
        $cmd->addOptionIfNotEmpty('--format', $this->format);
        return $process;
    }
}
