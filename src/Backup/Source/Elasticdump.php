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
 * Elasticdump source class.
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
class Elasticdump extends Binary implements Source
{
    /**
     * Show stdErr
     *
     * @var boolean
     */
    private $showStdErr;

    /**
     * Host to connect to
     *
     * @var string
     */
    private $host;

    /**
     * User to connect with
     *
     * @var string
     */
    private $user;

    /**
     * Password to authenticate with
     *
     * @var string
     */
    private $password;

    /**
     * Specific index to backup
     *
     * @var string
     */
    private $index;

    /**
     * Whether to backup the mapping or data
     * --type
     *
     * @var string
     */
    private $type;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = array())
    {
        $this->setupElasticdump($conf);
        $this->setupSourceData($conf);

        $this->host       = Util\Arr::getValue($conf, 'host', 'http://localhost:9200');
        $this->user       = Util\Arr::getValue($conf, 'user');
        $this->password   = Util\Arr::getValue($conf, 'password');
        $this->showStdErr = Util\Str::toBoolean(Util\Arr::getValue($conf, 'showStdErr', ''), false);
    }

    /**
     * Search for elasticdump command.
     *
     * @param array $conf
     */
    protected function setupElasticdump(array $conf)
    {
        if (empty($this->binary)) {
            $this->binary = $this->detectCommand('elasticdump', Util\Arr::getValue($conf, 'pathToElasticdump'));
        }
    }

    /**
     * Get index and type.
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->index = Util\Arr::getValue($conf, 'index');
        $this->type  = Util\Arr::getValue($conf, 'type');
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
        $exec        = $this->getExec($target);
        $elasticdump = $this->execute($exec, $target->getPathnamePlain(), $target->getCompressor());

        $result->debug($elasticdump->getCmd());

        if (!$elasticdump->wasSuccessful()) {
            throw new Exception('elasticdump failed');
        }

        return Status::create();
    }

    /**
     * Create the Exec to run the elasticdump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Cli\Exec
     * @throws Exception
     */
    public function getExec(Target $target)
    {
        if (null == $this->exec) {
            $this->exec = new Exec();
            $cmd        = new Cmd($this->binary);
            $this->exec->addCommand($cmd);

            // no std error unless it is activated
            if (!$this->showStdErr) {
                $cmd->silence();
                // i kill you
            }

            $cmd->addOption('--input', $this->generateNodeUrl($this->host, $this->user, $this->password, $this->index));

            $this->addOptionIfNotEmpty($cmd, '--type', $this->type);

            $cmd->addOption('--output', $target->getPathnamePlain());
        }

        return $this->exec;
    }

    /**
     * Create a elastic node url.
     *
     * @param  string $host
     * @param  string $user
     * @param  string $password
     * @param  string $index
     * @return string
     */
    private function generateNodeUrl($host, $user = null, $password = null, $index = null)
    {
        $parsed = parse_url($host);

        if (!isset($parsed['scheme'])) {
            $parsed = parse_url('http://' . $host);
        }

        $url = '';

        if (isset($parsed['scheme'])) {
            $url .= $parsed['scheme'] . '://';
        }

        if (!empty($user)) {
            $url .= $user;
        }

        if (!empty($password)) {
            $url .= ':' . $password;
        }

        if (!empty($user) || !empty($password)) {
            $url .= '@';
        }

        $url .= $parsed['host'];

        if (isset($parsed['port'])) {
            $url .= ':' . $parsed['port'];
        }

        if (!empty($parsed['path'])) {
            $url .= $parsed['path'];
        }

        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        if ($index !== null) {
            $url .= $index;
        }

        return $url;
    }
}
