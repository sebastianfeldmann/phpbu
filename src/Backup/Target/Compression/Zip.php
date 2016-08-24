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
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Zip extends Abstraction
{
    /**
     * Zip constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        parent::__construct($path);
        $this->cmd      = 'zip';
        $this->suffix   = 'zip';
        $this->mimeType = 'application/zip';
        $this->pipeable = false;
    }
}
