<?php
namespace phpbu\Backup;

use phpbu\App\Exception;
use phpbu\Backup\Compressor;
use phpbu\Util\String;

/**
 * Backup Target class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Target
{
    /**
     * Absolute path to the directory where to store the backup.
     *
     * @var string
     */
    private $dirname;

    /**
     * Backup filename.
     *
     * @var string
     */
    private $filename;

    /**
     * Permissions for potential directory or file creation.
     *
     * @var octal integer
     */
    private $permissions;

    /**
     * File compression.
     *
     * @var phpbu\Backup\Compressor
     */
    private $compressor;

    /**
     * Constructor
     *
     * @param  string $dirname
     * @param  string $filename
     * @throws phpbu\App\Exception
     */
    public function __construct($dirname, $filename)
    {
        $this->setDir($dirname);
        $this->setFile($filename);
    }

    /**
     * Permission setter.
     *
     * @param  string $permissions
     * @throws Exception
     */
    public function setPermissions($permissions)
    {
        if (empty($permissions)) {
            $permissions = 0700;
        } else {
            $oct = intval($permissions, 8);
            $dec = octdec($permissions);
            if ($dec < 1 || $dec > 261) {
                throw new Exception(sprintf('invalid permissions: %s', $permissions));
            }
            $permissions = $oct;
        }
        $this->permissions = $permissions;
    }

    /**
     * Directory setter.
     *
     * @param  string $dir
     * @param  string $mod
     * @throws phpbu\App\Exception
     */
    public function setDir($dir)
    {
        // replace potential date placeholder
        $dir = String::replaceDatePlaceholders($dir);
        // if directory doesn't exist, create it
        if (!is_dir($dir)) {
            $reporting = error_reporting();
            error_reporting(0);
            $created = mkdir($dir, 0755, true);
            error_reporting($reporting);
            if (!$created) {
                throw new Exception(sprintf('cant\'t create directory: %s', $dir));
            }
        }
        if (!is_writable($dir)) {
            throw new Exception(sprintf('no write permission for directory: %s', $dir));
        }
        $this->dirname = $dir;
    }

    /**
     * Filename setter.
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->filename = String::replaceDatePlaceholders($file);
    }

    /**
     * Date placeholder replacement.
     * Replaces %{somevalue} with date({somevalue}).
     *
     * @param  string $string
     * @return string
     */
    private function datePlaceholderDecode($string)
    {
        if (false !== strpos($string, '%')) {
            $string = preg_replace_callback(
                '#%([a-zA-Z])#',
                function ($match) {
                    return date($match[1]);
                },
                $string
            );
        }
        return $string;
    }

    /**
     * Path to target file.
     *
     * @param  boolean $compressed
     * @return string
     */
    public function getPath($compressed = true)
    {
        return $this->dirname
               . DIRECTORY_SEPARATOR
               . $this->filename
               . ($compressed && $this->shouldBeCompressed() ? '.' . $this->compressor->getSuffix() : '');
    }

    /**
     * Compressor setter.
     *
     * @param Compressor $compressor
     */
    public function setCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;
    }

    /**
     * Compressor getter.
     *
     * @return phpbu\Compressor
     */
    public function getCompressor()
    {
        return $this->compressor;
    }

    /**
     * Is a compressor set?
     *
     * @return boolean
     */
    public function shouldBeCompressed()
    {
        return $this->compressor !== null;
    }

    /**
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
