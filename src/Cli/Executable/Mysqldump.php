<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;

/**
 * Mysqldump Executable class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Mysqldump extends Abstraction implements Executable
{
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
    private $tablesToDump = array();

    /**
     * List of databases to backup
     * --databases array of strings
     *
     * @var array
     */
    private $databasesToDump = array();

    /**
     * List of tables to ignore
     *
     * @var array
     */
    private $tablesToIgnore = array();

    /**
     * List of tables where only the table structure is stored
     *
     * @var array
     */
    private $structureOnly = array();

    /**
     * Use mysqldump quick mode
     * -q
     *
     * @var boolean
     */
    private $quick = false;

    /**
     * Use mysqldump with compression
     * -C
     *
     * @var boolean
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
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        $this->cmd = 'mysqldump';
        parent::__construct($path);
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

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }
        $cmd->addOptionIfNotEmpty('--user', $this->user);
        $cmd->addOptionIfNotEmpty('--password', $this->password);
        $cmd->addOptionIfNotEmpty('--host', $this->host);
        $cmd->addOptionIfNotEmpty('-q', $this->quick, false);
        $cmd->addOptionIfNotEmpty('-C', $this->compress, false);
        $cmd->addOptionIfNotEmpty('--hex-blob', $this->hexBlob, false);

        if (count($this->tablesToDump)) {
            $cmd->addOption('--tables', $this->tablesToDump);
        } else {
            if (count($this->databasesToDump)) {
                $cmd->addOption('--databases', $this->databasesToDump);
            } else {
                $cmd->addOption('--all-databases');
            }
        }

        if (count($this->tablesToIgnore)) {
            foreach ($this->tablesToIgnore as $table) {
                $cmd->addOption('--ignore-table', $table);
            }
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
        $process->redirectOutputTo($this->dumpPathname);
        return $process;
    }
}
