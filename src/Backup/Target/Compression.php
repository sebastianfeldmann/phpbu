<?php
namespace phpbu\App\Backup\Target;

use SebastianFeldmann\Cli\Command;

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
interface Compression extends Command
{
    /**
     * Path getter.
     *
     * @return string
     */
    public function getPath() : string;

    /**
     * Returns the compressor suffix e.g. 'bz2'
     *
     * @return string
     */
    public function getSuffix() : string;

    /**
     * Returns the compressor mime type.
     *
     * @return string
     */
    public function getMimeType() : string;

    /**
     * Is the compression app pipeable.
     *
     * @return bool
     */
    public function isPipeable() : bool;
}
