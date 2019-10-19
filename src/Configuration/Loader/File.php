<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Adapter\Util;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Factory as AppFactory;
use phpbu\App\Util\Path as PathUtil;

/**
 * Base class for file based phpbu configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.1.2
 */
abstract class File
{
    /**
     * Path to config file
     *
     * @var string
     */
    protected $filename;

    /**
     * Path to config file
     *
     * @var array
     */
    protected $adapters;

    /**
     * Handling the bootstrapping
     *
     * @var \phpbu\App\Configuration\Bootstrapper
     */
    protected $bootstrapper;

    /**
     * List of validation errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * File constructor
     *
     * @param string                                $file
     * @param \phpbu\App\Configuration\Bootstrapper $bootstrapper
     */
    public function __construct(string $file, Configuration\Bootstrapper $bootstrapper)
    {
        $this->filename     = $file;
        $this->bootstrapper = $bootstrapper;
    }

    /**
     * Is the configuration valid
     *
     * @return bool
     */
    public function hasValidationErrors(): bool
    {
        return \count($this->errors) > 0;
    }

    /**
     * Return a list of all validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the phpbu Configuration
     *
     * @param  \phpbu\App\Factory $factory
     * @return \phpbu\App\Configuration
     * @throws \phpbu\App\Exception
     */
    public function getConfiguration(AppFactory $factory) : Configuration
    {
        // create configuration first so the working directory is available for all adapters
        $configuration = new Configuration();
        $configuration->setFilename($this->filename);

        $this->setAppSettings($configuration);
        $this->handleBootstrap($configuration);
        $this->setupAdapters($factory);

        $this->setLoggers($configuration);
        $this->setBackups($configuration);

        return $configuration;
    }

    /**
     * Load all available config adapters
     *
     * @param  \phpbu\App\Factory $factory
     * @throws \phpbu\App\Exception
     */
    protected function setupAdapters(AppFactory $factory)
    {
        foreach ($this->getAdapterConfigs() as $config) {
            $this->adapters[$config->name] = $factory->createAdapter($config->type, $config->options);
        }
    }

    /**
     * Return a registered adapter
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
     * Return list of adapter configs
     *
     * @return array
     */
    abstract protected function getAdapterConfigs();

    /**
     * Set the phpbu application settings
     *
     * @param  \phpbu\App\Configuration $configuration
     */
    abstract public function setAppSettings(Configuration $configuration);

    /**
     * Set the log configuration
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    abstract public function setLoggers(Configuration $configuration);

    /**
     * Set the backup configurations
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    abstract public function setBackups(Configuration $configuration);

    /**
     * Handles the bootstrap file inclusion
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    protected function handleBootstrap(Configuration $configuration)
    {
        $this->bootstrapper->run($configuration);
    }

    /**
     * Converts a path to an absolute one if necessary
     *
     * @param  string  $path
     * @param  boolean $useIncludePath
     * @return string
     */
    protected function toAbsolutePath($path, $useIncludePath = false)
    {
        return PathUtil::toAbsolutePath($path, dirname($this->filename), $useIncludePath);
    }

    /**
     * Return option value
     * Checks if the value should be fetched from an Adapter, if not it just returns the value.
     *
     * @param  string $value
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function getAdapterizedValue($value)
    {
        if (!empty($value)) {
            foreach (Util::getAdapterReplacements($value) as $replacement) {
                $search  = $replacement['search'];
                $replace = $this->getAdapter($replacement['adapter'])->getValue($replacement['path']);
                $value   = str_replace($search, $replace, $value);
            }
        }
        return $value;
    }

    /**
     * Load the file
     *
     * @param  string $filename
     * @throws \phpbu\App\Exception
     * @return string
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
