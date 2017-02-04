<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Factory as AppFactory;
use phpbu\App\Util\Cli;

/**
 *
 * Base class for file based phpbu configuration.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
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
     * Path to config file.
     *
     * @var array
     */
    protected $adapters;

    /**
     * File constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->filename = $file;
    }

    /**
     * Returns the phpbu Configuration.
     *
     * @param  \phpbu\App\Factory $factory
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration(AppFactory $factory)
    {
        // create configuration first so the working directory is available for all adapters
        $configuration = new Configuration();
        $configuration->setFilename($this->filename);

        $this->setupAdapters($factory);

        $this->setAppSettings($configuration);
        $this->setLoggers($configuration);
        $this->setBackups($configuration);

        return $configuration;
    }

    /**
     * Load all available config adapters.
     *
     * @param \phpbu\App\Factory $factory
     */
    protected function setupAdapters(AppFactory $factory)
    {
        foreach ($this->getAdapterConfigs() as $config) {
            $this->adapters[$config->name] = $factory->createAdapter($config->type, $config->options);
        }
    }

    /**
     * Return a registered adapter.
     *
     * @param  string $name
     * @return \phpbu\App\Adapter
     * @throws \phpbu\App\Exception
     */
    protected function getAdapter($name)
    {
        if (!isset($this->adapters[$name])) {
            throw new Exception('no adapter registered with name: ' . $name);
        }
        return $this->adapters[$name];
    }

    /**
     * Return list of adapter configs.
     *
     * @return array
     */
    abstract protected function getAdapterConfigs();

    /**
     * Set the phpbu application settings.
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    abstract public function setAppSettings(Configuration $configuration);

    /**
     * Set the log configuration.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    abstract public function setLoggers(Configuration $configuration);

    /**
     * Set the backup configurations.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    abstract public function setBackups(Configuration $configuration);

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
     * Return option value.
     * Checks if the value should be fetched from an Adapter, if not it just returns the value.
     *
     * @param  string $value
     * @return string
     */
    protected function getOptionValue($value)
    {
        $match = [];
        if (preg_match('#^adapter:([a-z0-9_\-]+):(.+)#i', $value, $match)) {
            $adapter = $match[1];
            $path    = $match[2];
            $value   = $this->getAdapter($adapter)->getValue($path);
        }
        return $value;
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
