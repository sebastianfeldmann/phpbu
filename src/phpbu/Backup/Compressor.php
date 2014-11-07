<?php
namespace phpbu\Backup;

use phpbu\App\Exception;

/**
 * Compressor
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Compressor
{
    /**
     * Path to command binary.
     *
     * @var string
     */
    private $path;

    /**
     * Command name.
     *
     * @var string
     */
    private $cmd;

    /**
     * Suffix for compressed files.
     *
     * @var string
     */
    private $suffix;

    /**
     * Constructor.
     *
     * @param string $cmd
     * @param string $suffix
     * @param string $pathToCmd without trailing slash
     */
    protected function __construct($cmd, $suffix, $pathToCmd = null)
    {
        $this->path   = $pathToCmd . (!empty($pathToCmd) ? DIRECTORY_SEPARATOR : '');
        $this->cmd    = $cmd;
        $this->suffix = $suffix;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->path . $this->cmd;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Factory method.
     *
     * @param  string $name
     * @throws Exception
     * @return Compressor
     */
    public static function create($name)
    {
        $path = null;
        // check if a path is given for the compressor
        if (basename($name) !== $name) {
            $path = dirname($name);
            $name = basename($name);
        }

        $availableCompressors = array(
            'gzip' => array(
                'gzip',
                'gz'
            ),
            'bzip2' => array(
                'bzip2',
                'bz2'
            ),
            'zip' => array(
                'zip',
                'zip'
            )
        );
        if (!isset($availableCompressors[$name])) {
            throw new Exception('invalid compressor:' . $name);
        }
        return new static($availableCompressors[$name][0], $availableCompressors[$name][1], $path);
    }
}
