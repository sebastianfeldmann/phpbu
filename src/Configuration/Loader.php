<?php
namespace phpbu\App\Configuration;

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
     * @return \phpbu\App\Configuration
     */
    public function getConfiguration();
}
