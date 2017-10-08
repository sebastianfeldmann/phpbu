<?php
namespace phpbu\App\Adapter;

use Dotenv\Dotenv as DotenvLib;
use phpbu\App\Adapter;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Util as AppUtil;

/**
 * PHPArray Adapter
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 4.0.1
 */
class PHPArray implements Adapter
{
    /**
     * Path to the config file
     *
     * @var string
     */
    private $file;

    /**
     * Configuration
     *
     * @var array
     */
    private $config;

    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     */
    public function setup(array $conf)
    {
        $path       = AppUtil\Arr::getValue($conf, 'file', '.env');
        $this->file = AppUtil\Cli::toAbsolutePath($path, Configuration::getWorkingDirectory());
        $this->load();
    }

    /**
     * Load config file to local file.
     *
     * @throws \phpbu\App\Exception
     */
    private function load()
    {
        if (!file_exists($this->file)) {
            throw new Exception('config file not found');
        }
        $this->config = require $this->file;
    }

    /**
     * Return a value for a given path.
     *
     * @param  string $path
     * @return string
     * @throws \phpbu\App\Exception
     */
    public function getValue(string $path) : string
    {
        $arrPath = explode('.', $path);
        $data    = $this->config;
        foreach ($arrPath as $segment) {
            if (!isset($data[$segment])) {
                throw new Exception('invalid config path');
            }
            $data = $data[$segment];
        }
        return (string) $data;
    }
}
