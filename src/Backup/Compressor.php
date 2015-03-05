<?php
namespace phpbu\Backup;

use phpbu\App\Exception;
use phpbu\Backup\Cli\Cmd;
use phpbu\Backup\Cli\Exec;

/**
 * Compressor
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Compressor
{
    /**
     * Path to command binary
     *
     * @var string
     */
    protected $path;

    /**
     * Command name
     *
     * @var string
     */
    protected $cmd;

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix;

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType;

    /**
     * List of available compressors
     *
     * @var array
     */
    protected static $availableCompressors = array(
        'gzip' => array(
            'suffix' => 'gz',
            'mime'   => 'application/x-gzip'
        ),
        'bzip2' => array(
            'suffix' => 'bz2',
            'mime'   => 'application/x-bzip2'
        ),
        'zip' => array(
            'suffix' => 'zip',
            'mime'   => 'application/zip'
        )
    );

    /**
     * Constructor.
     *
     * @param string $cmd
     * @param string $pathToCmd without trailing slash
     */
    protected function __construct($cmd, $pathToCmd = null)
    {
        $this->path     = $pathToCmd . (!empty($pathToCmd) ? DIRECTORY_SEPARATOR : '');
        $this->cmd      = $cmd;
        $this->suffix   = self::$availableCompressors[$cmd]['suffix'];
        $this->mimeType = self::$availableCompressors[$cmd]['mime'];
    }

    /**
     * Return the cli command.
     *
     * @param  boolean $includingPath
     * @return string
     */
    public function getCommand($includingPath = true)
    {
        return ($includingPath ? $this->path : '') . $this->cmd;
    }

    /**
     * Return Exec to actually execute the compressor command.
     *
     * @param  string $fileToCompress
     * @param  array  $options
     * @return \phpbu\Backup\CLi\Exec
     */
    public function getExec($fileToCompress, array $options = array())
    {
        $cmd = new Cmd($this->getCommand());
        foreach ($options as $opt) {
            $cmd->addOption($opt);
        }
        $cmd->addArgument($fileToCompress);

        $exec = new Exec();
        $exec->addCommand($cmd);

        return $exec;
    }

    /**
     * Returns the compressor suffix e.g. 'bzip2'
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Returns the compressor mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Factory method.
     *
     * @param  string $name
     * @throws \phpbu\App\Exception
     * @return \phpbu\Backup\Compressor
     */
    public static function create($name)
    {
        $path = null;
        // check if a path is given for the compressor
        if (basename($name) !== $name) {
            $path = dirname($name);
            $name = basename($name);
        }

        if (!isset(self::$availableCompressors[$name])) {
            throw new Exception('invalid compressor:' . $name);
        }
        return new static($name, $path);
    }
}
