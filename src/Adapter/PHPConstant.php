<?php
namespace phpbu\App\Adapter;

use phpbu\App\Adapter;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Util as AppUtil;

/**
 * PHPConstant Adapter
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.11
 */
class PHPConstant implements Adapter
{
    /**
     * Path to the config file
     *
     * @var string
     */
    private $file;

    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     * @throws Exception
     */
    public function setup(array $conf)
    {
        $path       = AppUtil\Arr::getValue($conf, 'file', 'config.php');
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
        require $this->file;
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
        if (!defined($path)) {
            throw new Exception('constant not defined');
        }
        return (string) constant($path);
    }
}
