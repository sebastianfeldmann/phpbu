<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Cmd;
use phpbu\App\Cli\Executable;
use phpbu\App\Cli\Process;
use phpbu\App\Exception;

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
 * @since      Class available since Release 2.1.0
 */
class Elasticdump extends Abstraction implements Executable
{
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
     * File to dump to.
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
        $this->cmd = 'elasticdump';
        parent::__construct($path);
    }

    /**
     * Set host to get data from.
     *
     * @param  string $host
     * @return \phpbu\App\Cli\Executable\Elasticdump
     */
    public function useHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set index to dump.
     *
     * @param  string $index
     * @return \phpbu\App\Cli\Executable\Elasticdump
     */
    public function dumpIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Set dump type.
     *
     * @param  string $type
     * @return \phpbu\App\Cli\Executable\Elasticdump
     */
    public function dumpType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set file to dump to.
     *
     * @param  string $pathname
     * @return \phpbu\App\Cli\Executable\Elasticdump
     */
    public function dumpTo($pathname)
    {
        $this->dumpPathname = $pathname;
        return $this;
    }

    /**
     * Set elastic credentials.
     *
     * @param  string $user
     * @param  string $password
     * @return \phpbu\App\Cli\Executable\Elasticdump
     */
    public function credentials($user = null, $password = null)
    {
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    /**
     * Subclass Process generator.
     *
     * @return \phpbu\App\Cli\Process
     * @throws \phpbu\App\Exception
     */
    protected function createProcess()
    {
        if (empty($this->host)) {
            throw new Exception('host is mandatory');
        }
        if (empty($this->dumpPathname)) {
            throw new Exception('no file to dump to');
        }

        $process = new Process();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

        // no std error unless it is activated
        if (!$this->showStdErr) {
            $cmd->silence();
            // i kill you
        }

        $cmd->addOption('--input', $this->generateNodeUrl($this->host, $this->user, $this->password, $this->index));
        $cmd->addOptionIfNotEmpty('--type', $this->type);
        $cmd->addOption('--output', $this->dumpPathname);

        return $process;
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
