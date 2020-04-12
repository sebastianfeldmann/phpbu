<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Mysqldump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump extends SimulatorExecutable implements Simulator, Restorable
{
    /**
     * Path to mysql executable.
     *
     * @var string
     */
    private $pathToMysql;

    /**
     * Path to mysqldump executable.
     *
     * @var string
     */
    private $pathToMysqldump;

    /**
     * Path to mysqlimport executable.
     *
     * @var string
     */
    private $pathToMysqlimport;

    /**
     * Host to connect to
     * --host <hostname>
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     * --port <port>
     *
     * @var int
     */
    private $port;

    /**
     * Port to connect to
     * --protocol <TCP|SOCKET|PIPE|MEMORY>
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
    private $tables;

    /**
     * List of databases to backup
     * --databases array of strings
     *
     * @var array
     */
    private $databases;

    /**
     * List of tables to ignore
     *
     * @var array
     */
    private $ignoreTables;

    /**
     * List of tables where only the table structure is stored
     *
     * @var array
     */
    private $structureOnly;

    /**
     * Table separated data files
     * --tab
     *
     * @var boolean
     */
    private $filePerTable;

    /**
     * Use mysqldump quick mode
     * -q
     *
     * @var boolean
     */
    private $quick;

    /**
     * Lock tables option
     * --lock-tables
     *
     * @var bool
     */
    private $lockTables;

    /**
     * Single Transaction option
     * --single-transaction
     *
     * @var bool
     */
    private $singleTransaction;

    /**
     * Use mysqldump with compression
     * -C
     *
     * @var boolean
     */
    private $compress;

    /**
     * Use mysqldump without extended insert
     * --skip-extended-insert
     *
     * @var boolean
     */
    private $skipExtendedInsert;

    /**
     * Dump blob fields as hex
     * --hex-blob
     *
     * @var boolean
     */
    private $hexBlob;

    /**
     * Dump only table structures
     * --no-data
     *
     * @var boolean
     */
    private $noData;

    /**
     * Add general transaction id statement
     * --set-gids-purged=['ON', 'OFF', 'AUTO']
     *
     * @var string
     */
    private $gtidPurged;

    /**
     * SSL CA
     * --ssl-ca
     *
     * @var string
     */
    private $sslCa;

    /**
     * Dump procedures and functions
     * --routines
     *
     * @var bool
     */
    private $routines;

    /**
     * Skip triggers
     * --skip-triggers
     *
     * @var bool
     */
    private $skipTriggers;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupSourceData($conf);

        $this->pathToMysql        = Util\Arr::getValue($conf, 'pathToMysql', '');
        $this->pathToMysqldump    = Util\Arr::getValue($conf, 'pathToMysqldump', '');
        $this->pathToMysqlimport  = Util\Arr::getValue($conf, 'pathToMysqlimport', '');
        $this->host               = Util\Arr::getValue($conf, 'host', '');
        $this->port               = Util\Arr::getValue($conf, 'port', 0);
        $this->protocol           = Util\Arr::getValue($conf, 'protocol', '');
        $this->user               = Util\Arr::getValue($conf, 'user', '');
        $this->password           = Util\Arr::getValue($conf, 'password', '');
        $this->gtidPurged         = Util\Arr::getValue($conf, 'gtidPurged', '');
        $this->sslCa              = Util\Arr::getValue($conf, 'sslCa', '');
        $this->hexBlob            = Util\Str::toBoolean(Util\Arr::getValue($conf, 'hexBlob', ''), false);
        $this->quick              = Util\Str::toBoolean(Util\Arr::getValue($conf, 'quick', ''), false);
        $this->lockTables         = Util\Str::toBoolean(Util\Arr::getValue($conf, 'lockTables', ''), false);
        $this->singleTransaction  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'singleTransaction', ''), false);
        $this->compress           = Util\Str::toBoolean(Util\Arr::getValue($conf, 'compress', ''), false);
        $this->skipExtendedInsert = Util\Str::toBoolean(Util\Arr::getValue($conf, 'skipExtendedInsert', ''), false);
        $this->noData             = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noData', ''), false);
        $this->filePerTable       = Util\Str::toBoolean(Util\Arr::getValue($conf, 'filePerTable', ''), false);
        $this->routines           = Util\Str::toBoolean(Util\Arr::getValue($conf, 'routines', ''), false);
        $this->skipTriggers       = Util\Str::toBoolean(Util\Arr::getValue($conf, 'skipTriggers', ''), false);

        // this doesn't fail, but it doesn't work, so throw an exception so the user understands
        if ($this->filePerTable && count($this->structureOnly)) {
            throw new Exception('\'structureOnly\' can not be used with the \'filePerTable\' option');
        }
    }

    /**
     * Get tables and databases to backup
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->tables        = Util\Str::toList(Util\Arr::getValue($conf, 'tables', ''));
        $this->databases     = Util\Str::toList(Util\Arr::getValue($conf, 'databases', ''));
        $this->ignoreTables  = Util\Str::toList(Util\Arr::getValue($conf, 'ignoreTables', ''));
        $this->structureOnly = Util\Str::toList(Util\Arr::getValue($conf, 'structureOnly', ''));
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
        // create the writable dump directory for tables files
        $dumpTarget = $this->getDumpTarget($target);
        if ($this->filePerTable && !is_dir($dumpTarget)) {
            $fileMode = 0777;

            if (PHP_OS !== 'WINNT') {
                // get the permissions of the first directory that exists
                $pathSegments = explode(DIRECTORY_SEPARATOR, $dumpTarget);

                do {
                    $filename = implode(DIRECTORY_SEPARATOR, $pathSegments);

                    if (is_dir($filename) === false) {
                        continue;
                    }

                    $fileMode = substr(fileperms($filename), -4);
                    break;
                } while (array_pop($pathSegments) !== null);
            }

            $old = umask(0);
            mkdir($dumpTarget, $fileMode, true);
            umask($old);
        }

        $mysqldump = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandPrintable());

        if (!$mysqldump->isSuccessful()) {
            throw new Exception('mysqldump failed:' . $mysqldump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Restore the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     * @return \phpbu\App\Backup\Source\Status
     */
    public function restore(Target $target, Plan $plan): Status
    {
        $executable = $this->createMysqlExecutable();

        if ($this->filePerTable) {
            $database    = $this->databases[0];
            $sourceTar   = $target->getPathname(true) . '.tar';
            $mysqlimport = $this->createMysqlimportExecutable('<table-file>', $database);

            $executable->useDatabase($database);
            $executable->useSourceFile('<table-file>');

            $mysqlCommand  = $executable->getCommandPrintable();
            $importCommand = $mysqlimport->getCommandPrintable();
            $mysqlComment  = 'Restore the structure, execute this for every table file';
            $importComment = 'Restore the data, execute this for every table file';

            $plan->addRestoreCommand('tar -xvf ' . $sourceTar, 'Extract the table files');
            $plan->addRestoreCommand($mysqlCommand, $mysqlComment);
            $plan->addRestoreCommand($importCommand, $importComment);
        } else {
            $executable->useSourceFile($target->getFilename(true));
            $plan->addRestoreCommand($executable->getCommandPrintable());
        }

        return Status::create()->uncompressedFile($target->getPathname());
    }

    /**
     * Create the Executable to run the mysqldump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Mysqldump($this->pathToMysqldump);
        $executable->credentials($this->user, $this->password)
                   ->useHost($this->host)
                   ->usePort($this->port)
                   ->useProtocol($this->protocol)
                   ->useQuickMode($this->quick)
                   ->lockTables($this->lockTables)
                   ->dumpBlobsHexadecimal($this->hexBlob)
                   ->addGTIDStatement($this->gtidPurged)
                   ->useSslCa($this->sslCa)
                   ->useCompression($this->compress)
                   ->skipExtendedInsert($this->skipExtendedInsert)
                   ->dumpTables($this->tables)
                   ->singleTransaction($this->singleTransaction)
                   ->dumpDatabases($this->databases)
                   ->ignoreTables($this->ignoreTables)
                   ->produceFilePerTable($this->filePerTable)
                   ->dumpNoData($this->noData)
                   ->dumpRoutines($this->routines)
                   ->skipTriggers($this->skipTriggers)
                   ->dumpStructureOnly($this->structureOnly)
                   ->dumpTo($this->getDumpTarget($target));
        // if compression is active and commands can be piped
        if ($this->isHandlingCompression($target)) {
            $executable->compressOutput($target->getCompression());
        }
        return $executable;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        // file_per_table creates a directory with all the files
        if ($this->filePerTable) {
            return Status::create()->uncompressedDirectory($this->getDumpTarget($target));
        }

        // if compression is active and commands can be piped
        // compression is handled via pipe
        if ($this->isHandlingCompression($target)) {
            return Status::create();
        }

        // default create uncompressed dump file
        return Status::create()->uncompressedFile($this->getDumpTarget($target));
    }

    /**
     * Can compression be handled via pipe operator.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return bool
     */
    private function isHandlingCompression(Target $target) : bool
    {
        return $target->shouldBeCompressed() && Util\Cli::canPipe() && $target->getCompression()->isPipeable();
    }

    /**
     * Return dump target path.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    private function getDumpTarget(Target $target) : string
    {
        return $target->getPathnamePlain() . ($this->filePerTable ? '.dump' : '');
    }

    /**
     * Create the Executable to run the mysql command.
     *
     * @return \phpbu\App\Cli\Executable\Mysql
     */
    private function createMysqlExecutable(): Executable\Mysql
    {
        $executable = new Executable\Mysql($this->pathToMysql);
        $executable->credentials($this->user, $this->password)
            ->useHost($this->host)
            ->usePort($this->port)
            ->useProtocol($this->protocol)
            ->useQuickMode($this->quick)
            ->useCompression($this->compress);

        return $executable;
    }

    /**
     * Create the Executable to run the mysqlimport command.
     *
     * @param string $sourceFilename
     * @param string $targetDatabase
     *
     * @return \phpbu\App\Cli\Executable\Mysqlimport
     */
    private function createMysqlimportExecutable(string $sourceFilename, string $targetDatabase): Executable\Mysqlimport
    {
        $executable = new Executable\Mysqlimport($this->pathToMysqlimport);
        $executable->setSourceAndTarget($sourceFilename, $targetDatabase)
            ->credentials($this->user, $this->password)
            ->useHost($this->host)
            ->usePort($this->port)
            ->useProtocol($this->protocol)
            ->useCompression($this->compress);

        return $executable;
    }
}
