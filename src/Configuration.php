<?php
namespace phpbu\App;

/**
 * Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Configuration
{
    /**
     * Filename
     *
     * @var string
     */
    private $filename;

    /**
     * Working directory
     *
     * @var string
     */
    private $workingDirectory;

    /**
     * Path to bootstrap file.
     *
     * @var string
     */
    private $bootstrap;

    /**
     * Verbose output
     *
     * @var bool
     */
    private $verbose = false;

    /**
     * Use colors in output.
     *
     * @var bool
     */
    private $colors = false;

    /**
     * Output debug information
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * Don't execute anything just pretend to
     *
     * @var bool
     */
    private $simulate = false;

    /**
     * List of include paths
     *
     * @var array
     */
    private $includePaths = [];

    /**
     * List of ini settings
     *
     * @var array
     */
    private $iniSettings = [];

    /**
     * List of logger configurations
     *
     * @var array
     */
    private $loggers = [];

    /**
     * List of backup configurations
     *
     * @var array
     */
    private $backups = [];

    /**
     * Constructor
     *
     * @param string $wd
     */
    public function __construct($wd = null)
    {
        $this->workingDirectory = $wd === null ? getcwd() : $wd;
    }

    /**
     * Filename setter.
     *
     * @param string $file
     */
    public function setFilename($file)
    {
        $this->filename         = $file;
        $this->workingDirectory = dirname($file);
    }

    /**
     * Filename getter.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Working directory setter.
     *
     * @param string $wd
     */
    public function setWorkingDirectory($wd)
    {
        $this->workingDirectory = $wd;
    }

    /**
     * Working directory getter.
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * Bootstrap setter.
     *
     * @param $file
     */
    public function setBootstrap($file)
    {
        $this->bootstrap = $file;
    }

    /**
     * Bootstrap getter.
     *
     * @return string
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    /**
     * Verbose setter.
     *
     * @param bool $bool
     */
    public function setVerbose($bool)
    {
        $this->verbose = $bool;
    }

    /**
     * Verbose getter.
     *
     * @return bool
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * Colors setter.
     *
     * @param bool $bool
     */
    public function setColors($bool)
    {
        $this->colors = $bool;
    }

    /**
     * Colors getter.
     *
     * @return bool
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Debug setter.
     *
     * @param bool $bool
     */
    public function setDebug($bool)
    {
        $this->debug = $bool;
    }

    /**
     * Debug getter.
     *
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Simulate setter.
     *
     * @param bool $bool
     */
    public function setSimulate($bool)
    {
        $this->simulate = $bool;
    }

    /**
     * Simulate getter.
     *
     * @return bool
     */
    public function isSimulation()
    {
        return $this->simulate;
    }

    /**
     * Add an include_path.
     *
     * @param string $path
     */
    public function addIncludePath($path)
    {
        $this->includePaths[] = $path;
    }

    /**
     * Get the list of include path.
     *
     * @return array
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * Add a ini settings.
     *
     * @param string $name
     * @param string $value
     */
    public function addIniSetting($name, $value)
    {
        $this->iniSettings[$name] = $value;
    }

    /**
     * Get the list of ini settings.
     *
     * @return array
     */
    public function getIniSettings()
    {
        return $this->iniSettings;
    }

    /**
     * Add a logger.
     * This accepts valid logger configs as well as valid Listener objects.
     *
     * @param  mixed $logger
     * @throws \phpbu\App\Exception
     */
    public function addLogger($logger)
    {
        if (!($logger instanceof Listener) && !($logger instanceof Configuration\Logger)) {
            throw new Exception('invalid logger, only \'Listener\' and valid logger configurations are accepted');
        }
        $this->loggers[] = $logger;
    }

    /**
     * Get the list of logger configurations.
     *
     * @return array
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * Add a Backup configuration.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     */
    public function addBackup(Configuration\Backup $backup)
    {
        $this->backups[] = $backup;
    }

    /**
     * Get the list of backup configurations.
     *
     * @return array
     */
    public function getBackups()
    {
        return $this->backups;
    }
}
