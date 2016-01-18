<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Source;
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
class Mysqldump extends Cli implements Source, Simulator
{
    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToMysqldump;

    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

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
     * Dump target location
     * @var string
     */
    private $dumpPathname;

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
        $this->showStdErr      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
        $this->hexBlob         = Util\Str::toBoolean(Util\Arr::getValue($conf, 'hexBlob', ''), false);
        $this->quick           = Util\Str::toBoolean(Util\Arr::getValue($conf, 'quick', ''), false);
        $this->lockTables      = Util\Str::toBoolean(Util\Arr::getValue($conf, 'lockTables', ''), false);
        $this->compress        = Util\Str::toBoolean(Util\Arr::getValue($conf, 'compress', ''), false);
        $this->extendedInsert  = Util\Str::toBoolean(Util\Arr::getValue($conf, 'extendedInsert', ''), false);
        $this->noData          = Util\Str::toBoolean(Util\Arr::getValue($conf, 'noData', ''), false);
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
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result)
    {
        // setup dump location and execute the dump
        $this->dumpPathname = $target->getPathnamePlain();
        $mysqldump          = $this->execute($target);

        $result->debug($mysqldump->getCmd());

        if (!$mysqldump->wasSuccessful()) {
            throw new Exception('mysqldump failed');
        }

        return Status::create()->uncompressed($this->dumpPathname);
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
                             ->dumpNoData($this->noData)
                             ->dumpStructureOnly($this->structureOnly)
                             ->dumpTo($this->dumpPathname)
                             ->showStdErr($this->showStdErr);
        }
        return $this->executable;
    }

    /**
     * Simulate the backup execution.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug($this->getExecutable($target)->getCommandLine());

    }
}
