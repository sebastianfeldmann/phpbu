<?php
namespace phpbu\App\Backup;

use phpbu\App\Exception;

/**
 * Compressor
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
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
    protected static $availableCompressors = [
        'gzip' => [
            'suffix' => 'gz',
            'mime'   => 'application/x-gzip'
        ],
        'bzip2' => [
            'suffix' => 'bz2',
            'mime'   => 'application/x-bzip2'
        ],
        'zip' => [
            'suffix' => 'zip',
            'mime'   => 'application/zip'
        ]
    ];

    /**
     * Constructor.
     *
     * @param string $cmd
     * @param string $pathToCmd without trailing slash
     */
    public function __construct($cmd, $pathToCmd = null)
    {
        $this->path     = $pathToCmd . (!empty($pathToCmd) ? DIRECTORY_SEPARATOR : '');
        $this->cmd      = $cmd;
        $this->suffix   = self::$availableCompressors[$cmd]['suffix'];
        $this->mimeType = self::$availableCompressors[$cmd]['mime'];
    }

    /**
     * Return the cli command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->cmd;
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
     * @return \phpbu\App\Backup\Compressor
     * @throws \phpbu\App\Exception
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
