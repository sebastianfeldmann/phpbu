<?php
namespace phpbu\Backup;

use phpbu\App\Exception;
use phpbu\Util\String;

/**
 * Backup Target class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Target
{
    /**
     * Absolute path to the directory where to store the backup.
     *
     * @var string
     */
    private $path;

    /**
     * Path to the backup with potential date placeholders like %d.
     *
     * @var string
     */
    private $pathRaw;

    /**
     * Indicates if the path changes over time.
     *
     * @var boolean
     */
    private $pathIsChanging = false;

    /**
     * Part of the path without placeholders
     *
     * @var string
     */
    private $pathNotChanging;

    /**
     * List of directories containing date placeholders
     *
     * @var array
     */
    private $pathElementsChanging = array();

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
     * Size in bytes
     *
     * @var integer
     */
    private $size;

    /**
     * Permissions for potential directory or file creation.
     *
     * @var integer (octal)
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
     * @var \phpbu\Backup\Compressor
     */
    private $compressor;

    /**
     * Constructor
     *
     * @param  string  $path
     * @param  string  $filename
     * @param  integer $time
     * @throws \phpbu\App\Exception
     */
    public function __construct($path, $filename, $time = null)
    {
        $this->setPath($path, $time);
        $this->setFile($filename, $time);
    }

    /**
     * Permission setter.
     *
     * @param  string $permissions
     * @throws \phpbu\App\Exception
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
     * @param  string  $path
     * @param  integer $time
     * @throws \phpbu\App\Exception
     */
    public function setPath($path, $time = null)
    {
        $this->pathRaw = $path;
        if (false !== strpos($path, '%')) {
            $this->pathIsChanging = true;
            // path should be absolute so we remove the root slash
            $dirs = explode('/', substr($this->pathRaw, 1));

            $this->pathNotChanging = '';
            $foundChangingElement  = false;
            foreach ($dirs as $d) {
                if ($foundChangingElement || false !== strpos($d, '%')) {
                    $this->pathElementsChanging[] = $d;
                    $foundChangingElement = true;
                } else {
                    $this->pathNotChanging .= DIRECTORY_SEPARATOR . $d;
                }
            }
            // replace potential date placeholder
            $path = String::replaceDatePlaceholders($path, $time);
        } else {
            $this->pathNotChanging = $path;
        }
        $this->path = $path;
    }

    /**
     * Filename setter.
     *
     * @param string  $file
     * @param integer $time
     */
    public function setFile($file, $time = null)
    {
        $this->filenameRaw = $file;
        if (false !== strpos($file, '%')) {
            $this->filenameIsChanging = true;
            $file                    = String::replaceDatePlaceholders($file, $time);
        }
        $this->filename = $file;
    }

    /**
     * Checks if the backup target directory is writable.
     * Creates the Directory if it doesn't exist.
     *
     * @throws \phpbu\App\Exception
     */
    public function setupPath()
    {
        //if directory doesn't exist, create it
        if (!is_dir($this->path)) {
            $reporting = error_reporting();
            error_reporting(0);
            $created = mkdir($this->path, 0755, true);
            error_reporting($reporting);
            if (!$created) {
                throw new Exception(sprintf('cant\'t create directory: %s', $this->path));
            }
        }
        if (!is_writable($this->path)) {
            throw new Exception(sprintf('no write permission for directory: %s', $this->path));
        }
    }

    /**
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPathRaw()
    {
        return $this->pathRaw;
    }

    /**
     * Return the name to the backup file.
     *
     * @param  boolean $compressed
     * @return string
     */
    public function getFilename($compressed = false)
    {
        return $this->filename . (
            $compressed && $this->shouldBeCompressed()
            ? '.' . $this->compressor->getSuffix()
            : ''
        );
    }

    /**
     * Return the name to the backup file.
     *
     * @return string
     */
    public function getFilenameRaw()
    {
        return $this->filenameRaw;
    }

    /**
     * Return the actual filesize in bytes
     *
     * @throws Exception
     * @return integer
     */
    public function getSize()
    {
        if (null === $this->size) {
            if (!file_exists($this)) {
                throw new Exception(sprintf('target file \'%s\' doesn\'t exist', $this->getFilenameCompressed()));
            }
            $this->size = filesize($this);
        }
        return $this->size;
    }

    /**
     * Target file exists already
     *
     * @param  boolean $compressed
     * @return boolean
     */
    public function fileExists($compressed = true)
    {
        return file_exists($this->getPathname($compressed));
    }

    /**
     * Deletes the target file
     *
     * @param  bool $compressed
     * @throws \phpbu\App\Exception
     */
    public function unlink($compressed = true)
    {
        if (!$this->fileExists($compressed)) {
            throw new Exception(sprintf('target file \'%s\' doesn\'t exist', $this->getFilename($compressed)));
        }
        if (!is_writable($this->getPathname($compressed))) {
            throw new Exception(sprintf('can\t delete file \'%s\'', $this->getFilename($compressed)));
        }
        $this->size = filesize($this->getPathname($compressed));
    }

    /**
     * Return the filename of the backup file
     *
     * @return string
     */
    public function getFilenameCompressed()
    {
        return $this->getFilename(true);
    }


    /**
     * Return path and filename of the backup file.
     *
     * @param  boolean $compressed
     * @return string
     */
    public function getPathname($compressed = false)
    {
        return $this->path
        . DIRECTORY_SEPARATOR
        . $this->filename
        . ($compressed && $this->shouldBeCompressed() ? '.' . $this->compressor->getSuffix() : '');
    }

    /**
     * Return the path and compressed filename of the backup file.
     *
     * @return string
     */
    public function getPathnameCompressed()
    {
        return $this->getPathname(true);
    }

    /**
     * Is dirname configured with any date placeholders
     *
     * @return boolean
     */
    public function hasChangingPath()
    {
        return $this->pathIsChanging;
    }

    /**
     * Return the part of the path that is not changing
     *
     * @return string
     */
    public function getPathThatIsNotChanging()
    {
        return $this->pathNotChanging;
    }

    /**
     * Changing path elements getter
     *
     * @return array
     */
    public function getChangingPathElements()
    {
        return $this->pathElementsChanging;
    }

    /**
     * Return amount of changing path elements
     *
     * @return integer
     */
    public function countChangingPathElements()
    {
        return count($this->pathElementsChanging);
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
     * Disable file compression
     */
    public function disableCompression()
    {
        $this->compress = false;
    }

    /**
     * Enable file compression
     *
     * @throws \phpbu\App\Exception
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
     * @param \phpbu\Backup\Compressor $compressor
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
     * Magic to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPathname(true);
    }
}
