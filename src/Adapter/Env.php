<?php
namespace phpbu\App\Adapter;

use phpbu\App\Adapter;

/**
 * Env Adapter
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 4.0.0
 */
class Env implements Adapter
{
    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     */
    public function setup(array $conf)
    {
        // do something fooish
    }

    /**
     * Return a value for a given path.
     *
     * @param  string $path
     * @return string
     */
    public function getValue($path)
    {
        return getenv($path);
    }
}
