<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Executable Compressor
 *
 * Compress a file via CLI commands like bzip2, gzip or zip.
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
interface Compressible
{
    /**
     * Compress the plain target file.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return string
     */
    public function compress(Target $target, Result $result) : string;

    /**
     * Return the cli executable.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    public function getExecutable(Target $target);

    /**
     * Return final archive file.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getArchiveFile(Target $target);
}
