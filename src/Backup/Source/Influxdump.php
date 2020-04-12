<?php

namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Influxdump source class.
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
class Influxdump extends SimulatorExecutable implements Simulator
{
    /**
     * Executable to handle influxDb command.
     *
     * @var \phpbu\App\Cli\Executable\Influxdump
     */
    protected $executable;

    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToInfluxdump;

    /**
     * Host to backup
     * -host <localhost>:<port>
     *
     * @var string
     */
    private $host;

    /**
     * Database to backup
     *
     * @var string
     */
    private $database;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->pathToInfluxdump = Util\Arr::getValue($conf, 'pathToInfluxdump', '');
        $this->host             = Util\Arr::getValue($conf, 'host', 'localhost:8088');
        $this->database         = Util\Arr::getValue($conf, 'database', '');
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
        $influxdb = $this->execute($target);

        $result->debug($this->getExecutable($target)->getCommandPrintable());

        if (!$influxdb->isSuccessful()) {
            throw new Exception('influxdb dump failed:' . $influxdb->getStdErr());
        }

        return $this->createStatus($target);
    }

    /**
     * Create the Executable to run the influxd command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\Influxdump($this->pathToInfluxdump);
        $executable
            ->useHost($this->host)
            ->dumpDatabases($this->database)
            ->dumpTo($target->getPathnamePlain());

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
        // default create uncompressed dump file
        return Status::create()->uncompressedDirectory($target->getPathnamePlain());
    }
}
