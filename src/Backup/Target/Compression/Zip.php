<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Zip
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Zip extends Abstraction
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd = 'zip';

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix = 'zip';

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType = 'application/zip';

    /**
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable = false;
}
