<?php
namespace phpbu\App\Configuration;

use phpbu\App\Exception;

/**
 * Finder class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Finder
{
    /**
     * List of default config file names.
     *
     * @var string[]
     */
    private $defaultConfigNames = [
        'phpbu.xml',
        'phpbu.xml.dist',
    ];

    /**
     * Try to find a configuration file.
     *
     * @param  string $path
     * @return string
     * @throws \InvalidArgumentException
     * @throws \phpbu\App\Exception
     */
    public function findConfiguration(string $path) : string
    {
        // check configuration argument
        // if configuration argument is a directory
        // check for default configuration files 'phpbu.xml' and 'phpbu.xml.dist'
        if (!empty($path)) {
            if (is_file($path)) {
                return realpath($path);
            }
            if (is_dir($path)) {
                return $this->findConfigurationInDir($path);
            }
            // no config found :(
            throw new Exception('Invalid path');
        }
        // no configuration argument search for default configuration files
        // 'phpbu.xml' and 'phpbu.xml.dist' in current working directory
        return $this->findConfigurationInDir(getcwd());
    }

    /**
     * Check directory for default configuration files phpbu.xml, phpbu.xml.dist.
     *
     * @param  string $path
     * @return string
     * @throws \phpbu\App\Exception
     */
    protected function findConfigurationInDir(string $path) : string
    {
        foreach ($this->defaultConfigNames as $file) {
            $configurationFile = $path . DIRECTORY_SEPARATOR . $file;
            if (file_exists($configurationFile)) {
                return realpath($configurationFile);
            }
        }
        throw new Exception('Can\'t find configuration in directory \'' . $path . '\'.');
    }
}
