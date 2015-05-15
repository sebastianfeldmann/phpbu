<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Util\Cli;

/**
 *
 * Base class for file based phpbu configuration.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.2
 */
abstract class File
{
    /**
     * Path to config file.
     *
     * @var string
     */
    protected $filename;

    /**
     * Returns the phpbu Configuration.
     *
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration()
    {
        $configuration = new Configuration();
        $configuration->setFilename($this->filename);

        $this->setAppSettings($configuration);
        $this->setPhpSettings($configuration);
        $this->setLoggers($configuration);
        $this->setBackups($configuration);

        return $configuration;
    }

    /**
     * Set the phpbu application settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    public abstract function setAppSettings(Configuration $configuration);

    /**
     * Set the php settings.
     * Checking for include_path and ini settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    public abstract function setPhpSettings(Configuration $configuration);

    /**
     * Set the log configuration.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public abstract function setLoggers(Configuration $configuration);

    /**
     * Set the backup configurations.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public abstract function setBackups(Configuration $configuration);

    /**
     * Converts a path to an absolute one if necessary.
     *
     * @param  string  $path
     * @param  boolean $useIncludePath
     * @return string
     */
    protected function toAbsolutePath($path, $useIncludePath = false)
    {
        return Cli::toAbsolutePath($path, dirname($this->filename), $useIncludePath);
    }

    /**
     * Load the file.
     *
     * @param  string $filename
     * @throws \phpbu\App\Exception
     * @return \stdClass
     */
    protected function loadFile($filename)
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);
        error_reporting($reporting);

        if ($contents === false) {
            throw new Exception(sprintf('Could not read "%s".', $filename));
        }
        return $contents;
    }
}
