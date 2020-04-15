<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Pgdump source class
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Pgdump extends SimulatorExecutable implements Simulator
{
    /**
     * pg_dump default format.
     *
     * @var string
     */
    const DEFAULT_FORMAT = 'p';

    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToPgdump;

    /**
     * Host to connect to
     * --host=<hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * --port=<PortNumber>
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
     * PGPASSWORD=<password>
     *
     * @var string
     */
    private $password;

    /**
     * List of databases to backup
     * --dbname string
     *
     * @var string
     */
    private $database;

    /**
     * List of schemas to backup
     * --schema=<schema> array of strings
     *
     * @var array
     */
    private $schemas;

    /**
     * List of schemas to ignore
     * --exclude-schema=<schema>
     *
     * @var array
     */
    private $excludeSchemas;

    /**
     * List of tables to backup
     * --table=<table> array of strings
     *
     * @var array
     */
    private $tables;

    /**
     * List of tables to ignore
     * --exclude-table=<table>
     *
     * @var array
     */
    private $excludeTables;

    /**
     * List of tables where only the table structure is stored
     *
     * @var array
     */
    private $excludeTableData;

    /**
     * Dump encoding
     * --encoding
     *
     * @var string
     */
    private $encoding;

    /**
     * Add drop statements.
     * --clean
     *
     * @var boolean
     */
    private $clean;

    /**
     * Dump no owner statement
     * --no-owner
     *
     * @var bool
     */
    private $noOwner;

    /**
     * Dump format
     * --format=<format>
     *
     * @var string
     */
    private $format;

    /**
     * Dump only table structures
     * --schema-only
     *
     * @var bool
     */
    private $schemaOnly;

    /**
     * Dump only table data
     * --data-only
     *
     * @var bool
     */
    private $dataOnly;

    /**
     * Dump no privilege data
     * --no-acl
     *
     * @var bool
     */
    private $noPrivileges;

    /**
     * Setup
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->pathToPgdump = Util\Arr::getValue($conf, 'pathToPgdump', '');

        $this->setupSourceData($conf);
        $this->setupConnection($conf);
        $this->setupDumpOptions($conf);
    }

    /**
     * Setup connection settings
     *
     * @param array $conf
     */
    private function setupConnection(array $conf)
    {
        $this->host     = Util\Arr::getValue($conf, 'host', '');
        $this->port     = Util\Arr::getValue($conf, 'port', 0);
        $this->user     = Util\Arr::getValue($conf, 'user', '');
        $this->password = Util\Arr::getValue($conf, 'password', '');
    }

    /**
     * Get tables and databases to backup
     *
     * @param array $conf
     */
    private function setupSourceData(array $conf)
    {
        $this->database         = Util\Arr::getValue($conf, 'database', '');
        $this->tables           = Util\Str::toList(Util\Arr::getValue($conf, 'tables', ''));
        $this->excludeTables    = Util\Str::toList(Util\Arr::getValue($conf, 'ignoreTables', ''));
        $this->schemas          = Util\Str::toList(Util\Arr::getValue($conf, 'schemas', ''));
        $this->excludeSchemas   = Util\Str::toList(Util\Arr::getValue($conf, 'ignoreTables', ''));
        $this->excludeTableData = Util\Str::toList(Util\Arr::getValue($conf, 'excludeTableData', ''));
    }

    /**
     * Setup some dump options
     *
     * @param array $conf
     */
    private function setupDumpOptions(array $conf)
    {
        $this->clean        = Util\Str::toBoolean(Util\Arr::getValue($conf, 'clean', ''), false);
        $this->noPrivileges = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noPrivileges', ''), false);
        $this->schemaOnly   = Util\Str::toBoolean(Util\Arr::getValue($conf, 'schemaOnly', ''), false);
        $this->dataOnly     = Util\Str::toBoolean(Util\Arr::getValue($conf, 'dataOnly', ''), false);
        $this->noOwner      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noOwner', ''), false);
        $this->encoding     = Util\Arr::getValue($conf, 'encoding', '');
        $this->format       = Util\Arr::getValue($conf, 'format', self::DEFAULT_FORMAT);
    }


    /**
     * Execute the backup
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result) : Status
    {
        $pgDump = $this->execute($target);
        $result->debug($pgDump->getCmdPrintable());

        if (!$pgDump->isSuccessful()) {
            throw new Exception('mysqldump failed:' . $pgDump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the pg_dump command
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Pgdump($this->pathToPgdump);
        $executable->credentials($this->user, $this->password)
                   ->useHost($this->host)
                   ->usePort($this->port)
                   ->dumpDatabase($this->database)
                   ->dumpSchemas($this->schemas)
                   ->excludeSchemas($this->excludeSchemas)
                   ->dumpTables($this->tables)
                   ->excludeTables($this->excludeTables)
                   ->excludeTableData($this->excludeTableData)
                   ->dumpSchemaOnly($this->schemaOnly)
                   ->dumpDataOnly($this->dataOnly)
                   ->dumpNoPrivileges($this->noPrivileges)
                   ->dumpNoOwner($this->noOwner)
                   ->dumpFormat($this->format)
                   ->dumpTo($target->getPathnamePlain());
        return $executable;
    }

    /**
     * Create backup status
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        return Status::create()->uncompressedFile($target->getPathnamePlain());
    }
}
