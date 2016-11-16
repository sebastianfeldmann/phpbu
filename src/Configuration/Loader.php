<?php
namespace phpbu\App\Configuration;

use phpbu\App\Factory;

/**
 * Configuration loader interface.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
interface Loader
{
    /**
     * Returns the phpbu Configuration.
     *
     * @param  \phpbu\App\Factory $factory
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration(Factory $factory);
}
