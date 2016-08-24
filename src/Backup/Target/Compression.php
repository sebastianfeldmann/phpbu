<?php
namespace phpbu\App\Backup\Target;

/**
 * Compression
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Compression
{
    /**
     * Return the cli command.
     *
     * @return string
     */
    public function getCommand();

    /**
     * Path getter.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the compressor suffix e.g. 'bz2'
     *
     * @return string
     */
    public function getSuffix();

    /**
     * Returns the compressor mime type.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Is the compression app pipeable.
     *
     * @return bool
     */
    public function isPipeable();
}
