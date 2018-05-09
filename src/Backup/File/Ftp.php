<?php
namespace phpbu\App\Backup\File;

/**
 * Ftp file class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Ftp extends Remote
{
    /**
     * @var resource
     */
    private $ftpConnection;

    /**
     * Ftp constructor.
     *
     * @param resource $ftpConnection
     * @param string   $filename
     */
    public function __construct($ftpConnection, string $filename)
    {
        $this->ftpConnection = $ftpConnection;
        $this->filename      = $filename;
        $this->pathname      = $filename;
        $this->size          = ftp_size($ftpConnection, $filename);
        $this->lastModified  = ftp_mdtm($ftpConnection, $filename);
    }

    /**
     * Deletes the file.
     */
    public function unlink()
    {
        ftp_delete($this->ftpConnection, $this->filename);
    }
}
