<?php
namespace phpbu\App\Configuration\Loader;

use phpbu\App\Configuration;
use phpbu\App\Configuration\Loader;
use phpbu\App\Exception;
use phpbu\App\Util\Arr;

/**
 *
 * Loader class for a phpbu JSON configuration file.
 *
 * Example JSON configuration file:
 * <code>
 * {
 *   "verbose": true,
 *   "colors": true,
 *   "debug": false,
 *   "logging": [
 *     {
 *       "type": "json",
 *       "target": "backup/json.log"
 *     }
 *   ],
 *   "backups": [
 *     {
 *       "name": "some dir",
 *       "source": {
 *         "type": "tar",
 *         "options": {
 *             "path": "some/path"
 *         }
 *       },
 *       "target": {
 *         "dirname": "backup",
 *         "filename": "tarball-%Y%m%d-%H%i.tar",
 *         "compress": "bzip2"
 *       },
 *       "checks": [
 *         {
 *           "type": "sizemin",
 *           "value": "1B"
 *         }
 *       ],
 *       "cleanup": {
 *         "type": "Capacity",
 *         "options": {
 *           "size": "5M"
 *         }
 *       }
 *     }
 *   ]
 * }
 * </code>
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.2
 */
class Json extends File implements Loader
{
    /**
     * Config file.
     *
     * @var array
     */
    private $json;

    /**
     * Constructor.
     *
     * @param  string $file
     * @throws \phpbu\App\Exception
     */
    public function __construct($file)
    {
        parent::__construct($file);
        $this->json = $this->loadJsonFile($file);
    }

    /**
     * Return list of adapter configs.
     *
     * @return array
     * @throws \phpbu\App\Exception
     */
    protected function getAdapterConfigs()
    {
        $adapters = [];
        if (isset($this->json['adapters'])) {
            foreach ($this->json['adapters'] as $a) {
                if (!isset($a['type'])) {
                    throw new Exception('invalid adapter configuration: type missing');
                }
                if (!isset($a['name'])) {
                    throw new Exception('invalid adapter configuration: name missing');
                }
                $adapters[] = new Configuration\Adapter($a['type'], $a['name'], $this->getOptions($a));
            }
        }
        return $adapters;
    }

    /**
     * Set the phpbu application settings.
     *
     * @param \phpbu\App\Configuration $configuration
     */
    public function setAppSettings(Configuration $configuration)
    {
        if (isset($this->json['bootstrap'])) {
            $configuration->setBootstrap($this->toAbsolutePath($this->json['bootstrap']));
        }
        if (isset($this->json['verbose'])) {
            $configuration->setVerbose($this->json['verbose']);
        }
        if (isset($this->json['colors'])) {
            $configuration->setColors($this->json['colors']);
        }
    }

    /**
     * Set the log configuration.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public function setLoggers(Configuration $configuration)
    {
        if (isset($this->json['logging'])) {
            foreach ($this->json['logging'] as $l) {
                if (!isset($l['type'])) {
                    throw new Exception('invalid logger configuration: type missing');
                }
                $type    = $l['type'];
                $options = $this->getOptions($l);
                if (isset($options['target'])) {
                    $options['target'] = $this->toAbsolutePath($options['target']);
                }
                // search for target attribute to convert to option
                if (isset($l['target'])) {
                    $options['target'] = $this->toAbsolutePath($l['target']);
                }
                $configuration->addLogger(new Configuration\Logger($type, $options));
            }
        }
    }

    /**
     * Set the backup configurations.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public function setBackups(Configuration $configuration)
    {
        if (!isset($this->json['backups'])) {
            throw new Exception('no backup configured');
        }
        foreach ($this->json['backups'] as $backup) {
            $configuration->addBackup($this->getBackupConfig($backup));
        }
    }

    /**
     * Get the config for a single backup node.
     *
     * @param  array $json
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Configuration\Backup
     */
    private function getBackupConfig(array $json)
    {
        $name          = Arr::getValue($json, 'name');
        $stopOnFailure = Arr::getValue($json, 'stopOnFailure', false);
        $backup        = new Configuration\Backup($name, $stopOnFailure);

        $backup->setSource($this->getSource($json));
        $backup->setTarget($this->getTarget($json));

        $this->setChecks($backup, $json);
        $this->setCrypt($backup, $json);
        $this->setSyncs($backup, $json);
        $this->setCleanup($backup, $json);

        return $backup;
    }

    /**
     * Get source configuration.
     *
     * @param  array $json
     * @return \phpbu\App\Configuration\Backup\Source
     * @throws \phpbu\App\Exception
     */
    protected function getSource(array $json)
    {
        if (!isset($json['source'])) {
            throw new Exception('backup requires exactly one source config');
        }
        if (!isset($json['source']['type'])) {
            throw new Exception('source requires type');
        }

        return new Configuration\Backup\Source($json['source']['type'], $this->getOptions($json['source']));
    }

    /**
     * Get Target configuration.
     *
     * @param  array $json
     * @return \phpbu\App\Configuration\Backup\Target
     * @throws \phpbu\App\Exception
     */
    protected function getTarget(array $json)
    {
        if (!isset($json['target'])) {
            throw new Exception('backup requires a target config');
        }
        $compress = Arr::getValue($json['target'], 'compress');
        $filename = Arr::getValue($json['target'], 'filename');
        $dirname  = Arr::getValue($json['target'], 'dirname');

        if ($dirname) {
            $dirname = $this->toAbsolutePath($dirname);
        }

        return new Configuration\Backup\Target($dirname, $filename, $compress);
    }

    /**
     * Set backup checks.
     *
     * @param \phpbu\App\Configuration\Backup $backup
     * @param array                           $json
     */
    protected function setChecks(Configuration\Backup $backup, array $json)
    {
        if (isset($json['checks'])) {
            foreach ($json['checks'] as $c) {
                $type  = Arr::getValue($c, 'type');
                $value = Arr::getValue($c, 'value');
                // skip invalid sanity checks
                if (!$type || !$value) {
                    continue;
                }
                $backup->addCheck(new Configuration\Backup\Check($type, $value));
            }
        }
    }

    /**
     * Set the crypt configuration.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  array                           $json
     * @throws \phpbu\App\Exception
     */
    protected function setCrypt(Configuration\Backup $backup, array $json)
    {
        if (isset($json['crypt'])) {
            if (!isset($json['crypt']['type'])) {
                throw new Exception('invalid crypt configuration: type missing');
            }
            $type    = $json['crypt']['type'];
            $skip    = Arr::getValue($json['crypt'], 'skipOnFailure', true);
            $options = $this->getOptions($json['crypt']);
            $backup->setCrypt(new Configuration\Backup\Crypt($type, $skip, $options));
        }
    }

    /**
     * Set backup sync configurations.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  array                           $json
     * @throws \phpbu\App\Exception
     */
    protected function setSyncs(Configuration\Backup $backup, array $json)
    {
        if (isset($json['syncs'])) {
            foreach ($json['syncs'] as $s) {
                if (!isset($s['type'])) {
                    throw new Exception('invalid sync configuration: attribute type missing');
                }
                $type    = $s['type'];
                $skip    = Arr::getValue($s, 'skipOnFailure', true);
                $options = $this->getOptions($s);
                $backup->addSync(new Configuration\Backup\Sync($type, $skip, $options));
            }
        }
    }

    /**
     * Set the cleanup configuration.
     *
     * @param  \phpbu\App\Configuration\Backup $backup
     * @param  array                           $json
     * @throws \phpbu\App\Exception
     */
    protected function setCleanup(Configuration\Backup $backup, array $json)
    {
        if (isset($json['cleanup'])) {
            if (!isset($json['cleanup']['type'])) {
                throw new Exception('invalid cleanup configuration: type missing');
            }
            $type    = $json['cleanup']['type'];
            $skip    = Arr::getValue($json['cleanup'], 'skipOnFailure', true);
            $options = $this->getOptions($json['cleanup']);
            $backup->setCleanup(new Configuration\Backup\Cleanup($type, $skip, $options));
        }
    }

    /**
     * Extracts all option tags.
     *
     * @param  array $json
     * @return array
     */
    protected function getOptions(array $json)
    {
        $options = isset($json['options']) ? $json['options'] : [];

        foreach ($options as $name => $value) {
            $options[$name] = $this->getOptionValue($value);
        }

        return $options;
    }

    /**
     * Load the JSON-File.
     *
     * @param  string $filename
     * @throws \phpbu\App\Exception
     * @return array
     */
    private function loadJsonFile($filename)
    {
        $contents  = $this->loadFile($filename);
        $reporting = error_reporting(0);
        $json      = json_decode($contents, true);
        error_reporting($reporting);

        if (!is_array($json)) {
            throw new Exception(sprintf('Error loading file "%s"', $filename));
        }
        return $json;
    }
}
