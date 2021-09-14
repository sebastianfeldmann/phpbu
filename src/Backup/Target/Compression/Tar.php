<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Tar
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.x
 * */
class Tar extends Abstraction
{
    /**
     * Command name
     *
     * @var string
     */
    protected $cmd = 'tar -cf';

    /**
     * Suffix for compressed files
     *
     * @var string
     */
    protected $suffix = 'tar';

    /**
     * MIME type for compressed files
     *
     * @var string
     */
    protected $mimeType = 'application/x-tar';

    /**
     * Can this compression compress piped output
     *
     * @var bool
     */
    protected $pipeable = true;
}
