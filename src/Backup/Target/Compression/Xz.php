<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Xz
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Xz extends Abstraction
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd = 'xz';

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix = 'xz';

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType = 'application/x-xz';

    /**
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable = true;
}
