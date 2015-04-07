<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli\Binary;
use phpbu\App\Backup\Cli\Cmd;
use phpbu\App\Backup\Cli\Exec;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
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
class XtraBackup extends Binary implements Source
{
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
     * The regex should match the full qualified name: mydatabase.mytable
     * --tables string
     *
     * @var string
     */
    private $include;

    /**
     * List of databases and/or tables to backup
     * Tables must e fully qualified: mydatabase.mytable
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
        $this->setupXtraBackup($conf);
        $this->setupSourceData($conf);

        $this->host       = Util\Arr::getValue($conf, 'host');
        $this->user       = Util\Arr::getValue($conf, 'user');
        $this->password   = Util\Arr::getValue($conf, 'password');
        $this->showStdErr = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
    }

    /**
     * Search for innobackupex command.
     *
     * @param array $conf
     */
    protected function setupXtraBackup(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = $this->detectCommand('innobackupex', Util\Arr::getValue($conf, 'pathToXtraBackup'));
        }
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
        $exec         = $this->getExec($target);
        $innobackupex = $this->execute($exec);

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
     * @return \phpbu\App\Backup\Cli\Exec
     * @throws Exception
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $dump       = $this->getDumpDir($target);
            $this->exec = new Exec();
            $cmd        = new Cmd($this->binary);
            $cmd2       = new Cmd($this->binary);
            $this->exec->addCommand($cmd);
            $this->exec->addCommand($cmd2);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $cmd->silence();
                $cmd2->silence();
                // i kill you
            }

            $cmd->addOption('--no-timestamp');
            $this->addOptionIfNotEmpty($cmd, '--user', $this->user);
            $this->addOptionIfNotEmpty($cmd, '--password', $this->password);
            $this->addOptionIfNotEmpty($cmd, '--host', $this->host);

            if (!empty($this->include)) {
                $cmd->addOption('--include', $this->include);
            } else if (count($this->databases)) {
                $cmd->addOption('--databases', implode(' ', $this->databases));
            }

            $cmd->addArgument($dump);

            $cmd2->addOption('--apply-log');
            $cmd2->addArgument($dump);
        }

        return $this->exec;
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
