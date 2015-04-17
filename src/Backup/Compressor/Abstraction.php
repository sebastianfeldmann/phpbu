<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Target;
use phpbu\App\Exception;
use phpbu\App\Result;

/**
 * Compressor base class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.0
 */
abstract class Abstraction extends Cli
{
    /**
     * Path to cli binary.
     *
     * @var string
     */
    protected $pathToCommand;

    /**
     * File to dir to compress.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param  string $path
     * @param  string $pathToCommand
     * @throws \phpbu\App\Exception
     */
    public function __construct($path, $pathToCommand = null)
    {
        if (empty($path)) {
            throw new Exception('no path to compress set');
        }
        if (!$this->isPathValid($path)) {
            throw new Exception('path to compress should be a file');
        }
        $this->path          = $path;
        $this->pathToCommand = $pathToCommand;
    }

    /**
     * Compress the configured directory.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Exception
     */
    public function compress(Target $target, Result $result)
    {
        if (!$target->shouldBeCompressed()) {
            throw new Exception('target should not be compressed');
        }

        $res = $this->execute($target);
        $result->debug($res->getCmd());

        if (0 !== $res->getCode()) {
            throw new Exception('Failed to \'compress\' file: ' . $this->path);
        }
    }

    /**
     * Validate path.
     *
     * @param  string $path
     * @return boolean
     */
    abstract public function isPathValid($path);
}
