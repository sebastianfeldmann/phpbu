<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Exception;

/**
 * Status class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.1
 */
class Status
{
    /**
     * Source handles compression by itself.
     *
     * @var boolean
     */
    private $handledCompression = true;

    /**
     * Is created backup a directory
     *
     * @var bool
     */
    private $dataPathIsDir = false;

    /**
     * Path to generated source data.
     *
     * @var string
     */
    private $dataPath;


    /**
     * Source does not handle compression.
     *
     * @deprecated use uncompressedFile instead
     * @param      string $path
     * @return     \phpbu\App\Backup\Source\Status
     */
    public function uncompressed($path)
    {
        return $this->uncompressedFile($path);
    }

    /**
     * Source does not handle compression.
     *
     * @param  string $path
     * @return \phpbu\App\Backup\Source\Status
     */
    public function uncompressedFile($path)
    {
        $this->handledCompression = false;
        $this->dataPath           = $path;
        return $this;
    }

    /**
     * @param $path
     * @return \phpbu\App\Backup\Source\Status
     */
    public function uncompressedDirectory($path)
    {
        $this->dataPathIsDir = true;
        return $this->uncompressedFile($path);
    }

    /**
     * Did the Source handle the compression.
     *
     * @return boolean
     */
    public function handledCompression()
    {
        return $this->handledCompression;
    }

    /**
     * Is created backup data a directory.
     *
     * @return bool
     */
    public function isDirectory()
    {
        return $this->dataPathIsDir;
    }

    /**
     * Return data location.
     *
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getDataPath()
    {
        if ($this->handledCompression) {
            throw new Exception('source already handled compression');
        }
        return $this->dataPath;
    }

    /**
     * Static constructor for fluent interface calls.
     *
     * @return \phpbu\App\Backup\Source\Status
     */
    public static function create()
    {
        return new self();
    }
}
