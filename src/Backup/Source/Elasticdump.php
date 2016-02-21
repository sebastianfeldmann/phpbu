<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.1
 */
class Elasticdump extends SimulatorExecutable implements Simulator
{
    /**
     * Path to elasticdump binary.
     *
     * @var string
     */
    private $pathToElasticdump;

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
        $this->setupSourceData($conf);

        // environment settings
        $this->pathToElasticdump = Util\Arr::getValue($conf, 'pathToElasticdump');

        $this->host       = Util\Arr::getValue($conf, 'host', 'http://localhost:9200');
        $this->user       = Util\Arr::getValue($conf, 'user');
        $this->password   = Util\Arr::getValue($conf, 'password');
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
        $elasticdump = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandLinePrintable());

        if (!$elasticdump->wasSuccessful()) {
            throw new Exception('elasticdump failed: ' . $elasticdump->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the elasticdump command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     * @throws \phpbu\App\Exception
     */
    public function getExecutable(Target $target)
    {
        if (null == $this->executable) {
            $this->executable = new Executable\Elasticdump($this->pathToElasticdump);
            $this->executable->useHost($this->host)
                             ->credentials($this->user, $this->password)
                             ->dumpIndex($this->index)
                             ->dumpType($this->type)
                             ->dumpTo($target->getPathnamePlain());
        }
        return $this->executable;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target)
    {
        return Status::create()->uncompressedFile($target->getPathnamePlain());
    }
}
