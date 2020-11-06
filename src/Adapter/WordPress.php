<?php
namespace phpbu\App\Adapter;

use phpbu\App\Adapter;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Util as AppUtil;

/**
 * PHPWordPress Adapter by @planetahuevo based on https://rokaweb.ir/read-wpconfig-php/
 * It reads the wordpress database variables and return them
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.11
 */
class WordPress implements Adapter
{
    /**
     * Path to the config file
     *
     * @var string
     */
    private $file;

    /**
     * Contents of wordpress configuration
     *
     * @var string
     */
    private $config;

    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     * @throws Exception
     */
    public function setup(array $conf)
    {
        $path       = AppUtil\Arr::getValue($conf, 'file', 'wp-config.php');
        $this->file = AppUtil\Path::toAbsolutePath($path, Configuration::getWorkingDirectory());
        $this->load();
    }

    /**
     * Load config file to local file.
     *
     * @throws Exception
     */
    private function load()
    {
        if (!file_exists($this->file)) {
            throw new Exception('config file not found');
        }
        // read contents of wordpress config
        // we can not require the configuration because it requires a shit load of other files
        $this->config = file_get_contents($this->file);
    }

    /**
     * Return value a the constant with the given name.
     *
     * @param  string $path
     * @return string
     * @throws Exception
     */
    public function getValue(string $path) : string
    {
        $regex = "/define.+?'" . $path . "'.+?'(.*?)'.+/";
        $match = [];
        if (!preg_match($regex, $this->config, $match)) {
            throw new Exception('constant ' . $path . ' not found');
        }
        return (string) $match[1];
    }
}
