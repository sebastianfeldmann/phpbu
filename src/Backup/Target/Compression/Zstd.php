<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Zstd
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Zstd extends Abstraction
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd = 'zstd';

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix = 'zst';

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType = 'application/zstd';

    /**
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable = true;
}
