<?php
namespace phpbu\App\Backup;

use phpbu\App\Exception;
use phpbu\App\Util\Str;

/**
 * Backup Target class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
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
    private $pathElementsChanging = [];

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
     * Constructor.
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
            $path = Str::replaceDatePlaceholders($path, $time);
        } else {
            $this->pathNotChanging = $path;
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
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
            $file                     = Str::replaceDatePlaceholders($file, $time);
        }
        $this->filename = $file;
    }

    /**
     * Append another suffix to the filename.
     * 
     * @param string $suffix
     */
    public function appendFileSuffix($suffix)
    {
        $this->filename .= '.' . $suffix;
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
     * Size setter.
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
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
     * Return as backup file object.
     *
     * @return \phpbu\App\Backup\File
     */
    public function toFile()
    {
        return new File(new \SplFileInfo($this->getPathname()));
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
     * Is the target already compressed.
     *
     * @return boolean
     */
    public function isCompressed()
    {
        return $this->shouldBeCompressed() ? file_exists($this->getPathname()) : false;
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
     * Disable file encryption.
     */
    public function disableEncryption()
    {
        $this->crypt = false;
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
