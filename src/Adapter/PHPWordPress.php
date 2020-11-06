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
class PHPWordPress implements Adapter
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
        // read content of 
        $this->config = @file_get_contents($this->file);
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
        if ( !in_array($path, ['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST'], true )) {
            throw new Exception('constant not valid');
        }
        $data    = $this->config;
        if( !$data ) {
            throw new Exception('config file empty');
        }
        $params = [
        'DB_NAME' => "/define.+?'DB_NAME'.+?'(.*?)'.+/",
        'DB_USER' => "/define.+?'DB_USER'.+?'(.*?)'.+/",
        'DB_PASSWORD' => "/define.+?'DB_PASSWORD'.+?'(.*?)'.+/",
        'DB_HOST' => "/define.+?'DB_HOST'.+?'(.*?)'.+/",
        ];
        $wpconstants = [];
        foreach( $params as $key => $value ) {
            $found = preg_match_all( $value, $data, $result );
            if( $found ) {
                    $wpconstants[ $key ] = $result[ 1 ][ 0 ];
            } else {
                $wpconstants[ $key ] = false;
            }
        }
        $data = $wpconstants[$path];
        return (string) $data;
    }
}
