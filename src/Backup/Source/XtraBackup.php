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
 * XtraBackup (using the innobackupex script) source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class XtraBackup extends Cli implements Source
{
    /**
     * Path to innobackupex command.
     *
     * @var string
     */
    private $pathToXtraBackup;

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
    public function setup(array $conf = array())
    {
        $this->setupSourceData($conf);

        $this->pathToXtraBackup = Util\Arr::getValue($conf, 'pathToXtraBackup');
        $this->host             = Util\Arr::getValue($conf, 'host');
        $this->user             = Util\Arr::getValue($conf, 'user');
        $this->password         = Util\Arr::getValue($conf, 'password');
        $this->showStdErr       = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
    }

    /**
     * Get tables and databases to backup.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->include       = Util\Arr::getValue($conf, 'include');
        $this->databases     = Util\Str::toList(Util\Arr::getValue($conf, 'databases'));
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
        $innobackupex = $this->execute($target);

        $result->debug($innobackupex->getCmd());

        if (!$innobackupex->wasSuccessful()) {
            throw new Exception('XtraBackup failed');
        }

        return Status::create()->uncompressed()->dataPath($this->getDumpDir($target));
    }

    /**
     * Create the Exec to run the innobackupex backup and apply-log commands.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Innobackupex($this->pathToXtraBackup);
            $this->executable->useHost($this->host)
                             ->credentials($this->user, $this->password)
                             ->dumpDatabases($this->databases)
                             ->including($this->include)
                             ->dumpTo($this->getDumpDir($target))
                             ->showStdErr($this->showStdErr);
        }

        return $this->executable;
    }

    /**
     * Get the XtraBackup dump directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getDumpDir(Target $target)
    {
        return $target->getPath() . '/dump';
    }
}
