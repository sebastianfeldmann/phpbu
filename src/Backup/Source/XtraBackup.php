<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * XtraBackup source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XtraBackup extends SimulatorExecutable implements Simulator
{
    /**
     * Path to innobackupex command.
     *
     * @var string
     */
    private $pathToXtraBackup;

    /**
     * Path to MySQL data directory
     *
     * @var string
     */
    private $dataDir;

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
    private $databases;

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

        $this->pathToXtraBackup = Util\Arr::getValue($conf, 'pathToXtraBackup', '');
        $this->dataDir          = Util\Arr::getValue($conf, 'dataDir', '');
        $this->host             = Util\Arr::getValue($conf, 'host', '');
        $this->user             = Util\Arr::getValue($conf, 'user', '');
        $this->password         = Util\Arr::getValue($conf, 'password', '');
    }

    /**
     * Get tables and databases to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->include   = Util\Arr::getValue($conf, 'include', '');
        $this->databases = Util\Str::toList(Util\Arr::getValue($conf, 'databases', ''));
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
    public function backup(Target $target, Result $result) : Status
    {
        $innobackupex = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandPrintable());

        if (!$innobackupex->isSuccessful()) {
            throw new Exception('XtraBackup failed: ' . $innobackupex->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the innobackupex backup and apply-log commands.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Innobackupex($this->pathToXtraBackup);
        $executable->useHost($this->host)
                   ->credentials($this->user, $this->password)
                   ->dumpDatabases($this->databases)
                   ->including($this->include)
                   ->dumpFrom($this->dataDir)
                   ->dumpTo($this->getDumpDir($target));
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
        return Status::create()->uncompressedDirectory($this->getDumpDir($target));
    }

    /**
     * Get the XtraBackup dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target) : string
    {
        return $target->getPath()->getPath() . '/dump';
    }
}
