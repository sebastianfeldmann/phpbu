<?php
namespace phpbu\App\Adapter;

use Dotenv\Dotenv as DotenvLib;
use phpbu\App\Adapter;
use phpbu\App\Configuration;
use phpbu\App\Util as AppUtil;

/**
 * Dotenv Adapter
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 4.0.0
 */
class Dotenv implements Adapter
{
    /**
     * Path to the .env file
     *
     * @var string
     */
    private $file;

    /**
     * Actual dot env reader
     *
     * @var \Dotenv\Dotenv
     */
    private $dotenv;

    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     */
    public function setup(array $conf)
    {
        $path         = AppUtil\Arr::getValue($conf, 'file', '.env');
        $this->file   = AppUtil\Cli::toAbsolutePath($path, Configuration::getWorkingDirectory());
        $this->dotenv = new DotenvLib(dirname($this->file), basename($this->file));
        $this->dotenv->load();
    }

    /**
     * Return a value for a given path.
     *
     * @param  string $path
     * @return string
     */
    public function getValue(string $path) : string
    {
        return (string) getenv($path);
    }
}
