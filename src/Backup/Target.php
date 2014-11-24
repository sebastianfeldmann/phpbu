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
     * Path to the backup with potential date placeholders like %d.
     *
     * @var string
     */
    private $dirnameRaw;

    /**
     * Indicates if the path changes over time.
     *
     * @var boolean
     */
    private $dirnameIsChanging = false;

    /**
     * Backup filename.
     *
     * @var string
     */
    private $filename;

    /**
     * Filename with potential date placeholders like %d.
     *
     * @var string
     */
    private $filenameRaw;

    /**
     * Indicates if the filename changes over time.
     *
     * @var boolean
     */
    private $filenameIsChanging = false;

    /**
     * Permissions for potential directory or file creation.
     *
     * @var octal integer
     */
    private $permissions;

    /**
     * Should the file be compressed.
     *
     * @var boolean
     */
    private $compress = false;

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
        $this->dirnameRaw = $dir;
        if (false !== strpos($dir, '%')) {
            $this->dirnameIsChanging = true;
            // replace potential date placeholder
            $dir = String::replaceDatePlaceholders($dir);
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
        $this->filenameRaw = $file;
        if (false !== strpos($file, '%')) {
            $this->filenameIsChanging = true;
            $file                    = String::replaceDatePlaceholders($file);
        }
        $this->filename = $file;
    }

    /**
     * Checks if the backup target directory is writable.
     * Creates the Directory if it doesn't exist.
     *
     * @throws Exception
     */
    public function setupDir()
    {
         // if directory doesn't exist, create it
         if (!is_dir($this->dirname)) {
             $reporting = error_reporting();
             error_reporting(0);
             $created = mkdir($this->dirname, 0755, true);
             error_reporting($reporting);
             if (!$created) {
                throw new Exception(sprintf('cant\'t create directory: %s', $this->dirname));
             }
         }
         if (!is_writable($this->dirname)) {
            throw new Exception(sprintf('no write permission for directory: %s', $this->dirname));
         }
    }

    /**
     * Returns the path to the backup file
     *
     * @param  boolean $raw
     * @return string
     */
    public function getPath($raw = false)
    {
        return $raw ? $this->dirnameRaw : $this->dirname;
    }

    /**
     * Returns the name to the backup file
     *
     * @param  boolean $raw
     * @return string
     */
    public function getName($raw = false)
    {
        return $raw ? $this->filenameRaw : $this->filename;
    }

    /**
     * Path and filename off the target file.
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->dirname
               . DIRECTORY_SEPARATOR
               . $this->filename
               . ($this->shouldBeCompressed() ? '.' . $this->compressor->getSuffix() : '');
    }

    /**
     * Disable file compression
     */
    public function disableCompression()
    {
        $this->compress = false;
    }

    /**
     * Enable file compression
     *
     * @throws phpbu\App\Exception
     */
    public function enableCompression()
    {
        if (null == $this->compressor) {
            throw new Exception('can\'t enable compression without a compressor');
        }
        $this->compress = true;
    }

    /**
     * Compressor setter.
     *
     * @param Compressor $compressor
     */
    public function setCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;
        $this->compress   = true;
    }

    /**
     * Compressor getter.
     *
     * @return \phpbu\Backup\Compressor
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
        return $this->compress !== false;
    }

    /**
     * Dirname configured with any date placeholders
     *
     * @return boolean
     */
    public function hasChangingPath()
    {
        return $this->dirnameIsChanging;
    }

    /**
     * Filename configured with any date placeholders
     *
     * @return boolean
     */
    public function hasChangingFilename()
    {
        return $this->filenameIsChanging;
    }

    /**
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPathname();
    }
}
