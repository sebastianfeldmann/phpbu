<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Bzip2
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Bzip2 extends Abstraction
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd = 'bzip2';

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix = 'bz2';

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType = 'application/x-bzip2';

    /**
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable = true;
}
