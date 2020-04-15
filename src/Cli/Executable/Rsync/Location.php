<?php
namespace phpbu\App\Cli\Executable\Rsync;

use phpbu\App\Exception;

/**
 * Rsync location class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.1.4
 */
class Location
{
    /**
     * Username
     *
     * @var string
     */
    private $user;

    /**
     * Hostname
     *
     * @var string
     */
    private $host;

    /**
     * Path
     *
     * @var string
     */
    private $path;

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Set host.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Set path.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Is path valid
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->path);
    }

    /**
     * To string method.
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Magic to string method
     *
     * @return string
     */
    public function __toString()
    {
        $return = '';
        if (!empty($this->host)) {
            // remote user
            if (!empty($this->user)) {
                $return .= $this->user . '@';
            }
            $return .= $this->host . ':';
        }
        $return .= $this->path;

        return $return;
    }
}
