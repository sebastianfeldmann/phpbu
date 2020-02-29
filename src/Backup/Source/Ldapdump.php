<?php

namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Ldapdump source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Julian Marié <julian.marie@free.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class Ldapdump extends SimulatorExecutable implements Simulator
{
    /**
     * Executable to handle ldapDb command.
     *
     * @var \phpbu\App\Cli\Executable\Ldapdump
     */
    protected $executable;

    /**
     * Path to executable.
     *
     * @var string $pathToLdapdump
     */
    private $pathToLdapdump;

    /**
     * Host to connect to
     * -h <hostname>
     *
     * @var string $host
     */
    private $host;

    /**
     * Port to connect to
     * -p <port>
     *
     * @var string $port
     */
    private $port;

    /**
     * Basename
     * -b <basename>
     *
     * @var string $searchBase
     */
    private $searchBase;

    /**
     * BindDn to connect with
     * -D <DN>
     *
     * @var string $bindDn
     */
    private $bindDn;

    /**
     * Password to authenticate with
     * -w <password>
     *
     * @var string $password
     */
    private $password;

    /**
     * Filter
     * <filter>
     *
     * @var string $filter
     */
    private $filter;

    /**
     * Attributes
     * <attrs>
     *
     * @var array $attrs
     */
    private $attrs;

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

        $this->pathToLdapdump = Util\Arr::getValue($conf, 'pathToLdapdump', '');
        $this->host           = Util\Arr::getValue($conf, 'host', '');
        $this->port           = Util\Arr::getValue($conf, 'port', 0);
        $this->searchBase     = Util\Arr::getValue($conf, 'searchBase', '');
        $this->bindDn         = Util\Arr::getValue($conf, 'bindDn', '');
        $this->password       = Util\Arr::getValue($conf, 'password', '');
        $this->filter         = Util\Arr::getValue($conf, 'filter', '');
    }

    /**
     * Get attributes to backup
     *
     * @param array $conf
     */
    protected function setupSourceData(array $conf)
    {
        $this->attrs = Util\Str::toList(Util\Arr::getValue($conf, 'attrs', ''));
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
        $ldapdb = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandPrintable());

        if (!$ldapdb->isSuccessful()) {
            throw new Exception('ldapdb dump failed:' . $ldapdb->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the ldapdump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Ldapdump($this->pathToLdapdump);
        $executable
            ->credentials($this->bindDn, $this->password)
            ->useHost($this->host)
            ->usePort((int) $this->port)
            ->useSearchBase($this->searchBase)
            ->useFilter($this->filter)
            ->useAttributes($this->attrs)
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
        return $target->getPathnamePlain();
    }
}
