<?php
namespace phpbu\App\Backup\Target\Compression;

use phpbu\App\Backup\Target\Compression;

/**
 * Abstraction
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
abstract class Abstraction implements Compression
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd;

    /**
     * Path to command binary
     *
     * @var string
     */
    protected $path;

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
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    /**
     * Return the cli command.
     *
     * @return string
     */
    public function getCommand() : string
    {
        return $this->cmd;
    }

    /**
     * Path getter.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Returns the compressor suffix e.g. 'bz2'.
     *
     * @return string
     */
    public function getSuffix() : string
    {
        return $this->suffix;
    }

    /**
     * Is the compression app pipeable.
     *
     * @return bool
     */
    public function isPipeable() : bool
    {
        return $this->pipeable;
    }

    /**
     * Returns the compressor mime type.
     *
     * @return string
     */
    public function getMimeType() : string
    {
        return $this->mimeType;
    }
}
