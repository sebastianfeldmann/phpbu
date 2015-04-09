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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
    private $handledCompression;

    /**
     * Path to generated source data.
     *
     * @var string
     */
    private $dataPath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->handledCompression = true;
    }

    /**
     * Source doesn't handle compression.
     *
     * @return \phpbu\App\Backup\Source\Status
     */
    public function uncompressed()
    {
        $this->handledCompression = false;
        return $this;
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
     * Add a data location.
     *
     * @param  string $path
     * @return \phpbu\App\Backup\Source\Status
     */
    public function dataPath($path)
    {
        $this->dataPath = $path;
        return $this;
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
