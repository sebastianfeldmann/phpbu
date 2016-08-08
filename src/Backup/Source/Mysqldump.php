<?php
namespace phpbu\App\Backup\Source;

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
class Mysqldump extends SimulatorExecutable implements Simulator
{
    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToMysqldump;

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
     * The compressor cmd
     *
     * @var boolean
     */
    private $compressor;

    /**
     * Use mysqldump quick mode
     * -q
     *
     * @var boolean
     */
    private $quick;

    /**
     *
     * Lock tables option
     * --lock-tables
     *
     * @var bool
     */
    private $lockTables;

    /**
     * Use mysqldump with compression
     * -C
     *
     * @var boolean
     */
    private $compress;

    /**
     * Use mysqldump with extended insert
     * -e
     *
     * @var boolean
     */
    private $extendedInsert;

    /**
     * Dump blob fields as hex.
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
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->setupSourceData($conf);

        $this->pathToMysqldump = Util\Arr::getValue($conf, 'pathToMysqldump');
        $this->host            = Util\Arr::getValue($conf, 'host');
        $this->user            = Util\Arr::getValue($conf, 'user');
        $this->password        = Util\Arr::getValue($conf, 'password');
        $this->hexBlob         = Util\Str::toBoolean(Util\Arr::getValue($conf, 'hexBlob', ''), false);
        $this->quick           = Util\Str::toBoolean(Util\Arr::getValue($conf, 'quick', ''), false);
        $this->lockTables      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'lockTables', ''), false);
        $this->compress        = Util\Str::toBoolean(Util\Arr::getValue($conf, 'compress', ''), false);
        $this->extendedInsert  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'extendedInsert', ''), false);
        $this->noData          = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noData', ''), false);
        $this->filePerTable    = Util\Str::toBoolean(Util\Arr::getValue($conf, 'filePerTable', ''), false);
        $this->compressor      = Util\Arr::getValue($conf, 'compressor', '');

        // this doesn't fail, but it doesn't work, so throw an exception so the user understands
        if ($this->filePerTable && count($this->structureOnly)) {
            throw new Exception('\'structureOnly\' can not be used with the \'filePerTable\' option');
        }
    }

    /**
     * Get tables and databases to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->tables        = Util\Str::toList(Util\Arr::getValue($conf, 'tables'));
        $this->databases     = Util\Str::toList(Util\Arr::getValue($conf, 'databases'));
        $this->ignoreTables  = Util\Str::toList(Util\Arr::getValue($conf, 'ignoreTables'));
        $this->structureOnly = Util\Str::toList(Util\Arr::getValue($conf, 'structureOnly'));
    }

    /**
     * Execute the backup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        // create the writable dump directory for tables files
        if ($this->filePerTable && !is_dir($this->getDumpTarget($target))) {
            $old = umask(0);
            mkdir($this->getDumpTarget($target), 0777, true);
            umask($old);
        }

        $mysqldump = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandLinePrintable());

        if (!$mysqldump->wasSuccessful()) {
            throw new Exception('mysqldump failed:' . $mysqldump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the mysqldump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Mysqldump($this->pathToMysqldump);
            $this->executable->credentials($this->user, $this->password)
                             ->useHost($this->host)
                             ->useQuickMode($this->quick)
                             ->lockTables($this->lockTables)
                             ->dumpBlobsHexadecimal($this->hexBlob)
                             ->useCompression($this->compress)
                             ->useExtendedInsert($this->extendedInsert)
                             ->dumpTables($this->tables)
                             ->dumpDatabases($this->databases)
                             ->ignoreTables($this->ignoreTables)
                             ->produceFilePerTable($this->filePerTable)
                             ->dumpNoData($this->noData)
                             ->dumpStructureOnly($this->structureOnly)
                             ->dumpTo($this->getDumpTarget($target));

            if (!empty($this->compressor)) {
                $this->executable->getProcess()->setCompression($this->compressor);
            }
        }
        return $this->executable;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target)
    {
        return $this->filePerTable
            ? Status::create()->uncompressedDirectory($this->getDumpTarget($target))
            : Status::create()->uncompressedFile($this->getDumpTarget($target));
    }

    /**
     * Return dump target path.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    private function getDumpTarget(Target $target)
    {
        return $target->getPathnamePlain() . ($this->filePerTable ? '.dump' : '');
    }
}
