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
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Bzip2 extends Abstraction
{
    /**
     * Bzip2 constructor.
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        parent::__construct($path);
        $this->cmd      = 'bzip2';
        $this->suffix   = 'bz2';
        $this->mimeType = 'application/x-bzip2';
        $this->pipeable = true;
    }
}
