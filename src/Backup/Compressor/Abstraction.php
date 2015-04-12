<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable\Compressor;
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
 * @since      Class available since Release 2.0.2
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
     * Validate path.
     *
     * @param  string $path
     * @return boolean
     */
    abstract public function isPathValid($path);
}
