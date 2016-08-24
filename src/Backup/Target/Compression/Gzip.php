<?php
namespace phpbu\App\Backup\Target\Compression;

/**
 * Gzip
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Gzip extends Abstraction
{
    /**
     * Gzip constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        parent::__construct($path);
        $this->cmd      = 'gzip';
        $this->suffix   = 'gz';
        $this->mimeType = 'application/x-gzip';
        $this->pipeable = true;
    }
}
