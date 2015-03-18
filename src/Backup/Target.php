<?php
namespace phpbu\App\Backup;

use phpbu\App\Exception;
use phpbu\App\Util\String;

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
     * Target MIME type
     *
     * @var string
     */
    private $mimeType = 'text/plain';

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
     * @var \phpbu\App\Backup\Compressor
     */
    private $compressor;

    /**
     * Should the file be encrypted.
     *
     * @var boolean
     */
    private $crypt = false;

    /**
     * File crypter.
     *
     * @var \phpbu\App\Backup\Crypter
     */
    private $crypter;

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
                    $foundChangingElement         = true;
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
            $file                     = String::replaceDatePlaceholders($file, $time);
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
        // if directory doesn't exist, create it
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
     * Target file MIME type setter.
     *
     * @param string $mime
     */
    public function setMimeType($mime)
    {
        $this->mimeType = $mime;
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
            $dec = octdec($oct);
            if ($dec < 1 || $dec > octdec(0777)) {
                throw new Exception(sprintf('invalid permissions: %s', $permissions));
            }
            $permissions = $oct;
        }
        $this->permissions = $permissions;
    }

    /**
     * Permission getter.
     *
     * @return integer
     */
    public function getPermissions()
    {
        return $this->permissions;
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
     * @param  boolean $plain
     * @return string
     */
    public function getFilename($plain = false)
    {
        $suffix = '';
        if (!$plain) {
            $suffix .= $this->shouldBeCompressed() ? '.' . $this->compressor->getSuffix() : '';
            $suffix .= $this->shouldBeEncrypted() ? '.' . $this->crypter->getSuffix() : '';
        }
        return $this->filename . $suffix;
    }

    /**
     * Return the name of the backup file without compressor or encryption suffix.
     *
     * @return string
     */
    public function getFilenamePlain()
    {
        return $this->getFilename(true);
    }

    /**
     * Return the raw name of the backup file incl. date placeholder.
     *
     * @return string
     */
    public function getFilenameRaw()
    {
        return $this->filenameRaw;
    }

    /**
     * Return file MIME type.
     *
     * @return string
     */
    public function getMimeType()
    {
        $mimeType = $this->mimeType;
        if ($this->shouldBeCompressed()) {
            $mimeType = $this->compressor->getMimeType();
        }
        return $mimeType;
    }

    /**
     * Return the actual filesize in bytes.
     *
     * @throws Exception
     * @return integer
     */
    public function getSize()
    {
        if (null === $this->size) {
            if (!file_exists($this)) {
                throw new Exception(sprintf('target file \'%s\' doesn\'t exist', $this->getFilename()));
            }
            $this->size = filesize($this);
        }
        return $this->size;
    }

    /**
     * Target file exists already.
     *
     * @param  boolean $plain
     * @return boolean
     */
    public function fileExists($plain = false)
    {
        return file_exists($this->getPathname($plain));
    }

    /**
     * Deletes the target file.
     *
     * @param  boolean $plain
     * @throws \phpbu\App\Exception
     */
    public function unlink($plain = false)
    {
        if (!$this->fileExists($plain)) {
            throw new Exception(sprintf('target file \'%s\' doesn\'t exist', $this->getFilename($plain)));
        }
        if (!is_writable($this->getPathname($plain))) {
            throw new Exception(sprintf('can\t delete file \'%s\'', $this->getFilename($plain)));
        }
        unlink($this->getPathname($plain));
    }

    /**
     * Return path and filename of the backup file.
     *
     * @param  boolean $plain
     * @return string
     */
    public function getPathname($plain = false)
    {
        return $this->path
        . DIRECTORY_SEPARATOR
        . $this->getFilename($plain);
    }

    /**
     * Return path and plain filename of the backup file.
     *
     * @return string
     */
    public function getPathnamePlain()
    {
        return $this->getPathname(true);
    }

    /**
     * Is dirname configured with any date placeholders.
     *
     * @return boolean
     */
    public function hasChangingPath()
    {
        return $this->pathIsChanging;
    }

    /**
     * Return the part of the path that is not changing.
     *
     * @return string
     */
    public function getPathThatIsNotChanging()
    {
        return $this->pathNotChanging;
    }

    /**
     * Changing path elements getter.
     *
     * @return array
     */
    public function getChangingPathElements()
    {
        return $this->pathElementsChanging;
    }

    /**
     * Return amount of changing path elements.
     *
     * @return integer
     */
    public function countChangingPathElements()
    {
        return count($this->pathElementsChanging);
    }

    /**
     * Filename configured with any date placeholders.
     *
     * @return boolean
     */
    public function hasChangingFilename()
    {
        return $this->filenameIsChanging;
    }

    /**
     * Disable file compression.
     */
    public function disableCompression()
    {
        $this->compress = false;
    }

    /**
     * Enable file compression.
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
     * @param \phpbu\App\Backup\Compressor $compressor
     */
    public function setCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;
        $this->compress   = true;
    }

    /**
     * Compressor getter.
     *
     * @return \phpbu\App\Backup\Compressor
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
     * Disable file encryption.
     */
    public function disableEncryption()
    {
        $this->crypt = false;
    }

    /**
     * Enable file compression.
     *
     * @throws \phpbu\App\Exception
     */
    public function enableEncryption()
    {
        if (null == $this->crypter) {
            throw new Exception('can\'t enable encryption without a crypter');
        }
        $this->crypt = true;
    }

    /**
     * Crypter setter.
     *
     * @param \phpbu\App\Backup\Crypter $crypter
     */
    public function setCrypter(Crypter $crypter)
    {
        $this->crypter = $crypter;
        $this->crypt   = true;
    }

    /**
     * Crypter getter.
     *
     * @return \phpbu\App\Backup\Crypter
     */
    public function getCrypter()
    {
        return $this->crypter;
    }

    /**
     * Is a crypter set?
     *
     * @return boolean
     */
    public function shouldBeEncrypted()
    {
        return $this->crypt !== false;
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
