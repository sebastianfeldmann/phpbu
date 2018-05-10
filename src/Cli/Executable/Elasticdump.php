<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Util;
use SebastianFeldmann\Cli\CommandLine;
use SebastianFeldmann\Cli\Command\Executable as Cmd;

/**
 * Elasticdump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Francis Chuang <francis.chuang@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class Elasticdump extends Abstraction implements Executable
{
    use OptionMasker;

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
    public function __construct(string $path = '')
    {
        $this->setup('elasticdump', $path);
        $this->setMaskCandidates(['password']);
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
     * Elasticdump CommandLine generator.
     *
     * @return \SebastianFeldmann\Cli\CommandLine
     * @throws \phpbu\App\Exception
     */
    protected function createCommandLine() : CommandLine
    {
        if (empty($this->host)) {
            throw new Exception('host is mandatory');
        }
        if (empty($this->dumpPathname)) {
            throw new Exception('no file to dump to');
        }

        $process = new CommandLine();
        $cmd     = new Cmd($this->binary);
        $process->addCommand($cmd);

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

        // make sure there is a scheme
        if (!isset($parsed['scheme'])) {
            $parsed = parse_url('http://' . $host);
        }

        $url = $parsed['scheme'] . '://' . $this->getAuthUrlSnippet($user, $password) . $parsed['host'];

        if (isset($parsed['port'])) {
            $url .= ':' . $parsed['port'];
        }

        if (!empty($parsed['path'])) {
            $url .= $parsed['path'];
        }

        $url = Util\Path::withTrailingSlash($url);

        if ($index !== null) {
            $url .= $index;
        }

        return $url;
    }

    /**
     * Generate user password url snippet
     *
     * @param  string $user
     * @param  string $password
     * @return string
     */
    protected function getAuthUrlSnippet($user, $password)
    {
        $url = $user;

        if (!empty($password)) {
            $url .= ':' . $password;
        }

        if (!empty($url)) {
            $url .= '@';
        }

        return $url;
    }
}
