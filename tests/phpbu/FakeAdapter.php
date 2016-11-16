<?php
namespace phpbu\App;

/**
 * Fake Adapter
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.2.0
 */
class FakeAdapter implements Adapter
{
    /**
     * @var array
     */
    public $conf;

    /**
     * Setup the adapter.
     *
     * @param  array $conf
     * @return void
     */
    public function setup(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * Return a value for a given path.
     *
     * @param  string $path
     * @return string
     */
    public function getValue($path)
    {
        return 'secret';
    }
}
