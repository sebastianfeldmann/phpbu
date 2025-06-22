<?php
namespace phpbu\App\Configuration;

use phpbu\App\Configuration;
use phpbu\App\Factory;

/**
 * Configuration loader interface.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
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
    public function getConfiguration(Factory $factory) : Configuration;

    /**
     * Is the configuration valid
     *
     * @return bool
     */
    public function hasValidationErrors() : bool;

    /**
     * Return a list of all validation errors
     *
     * @return array
     */
    public function getValidationErrors() : array;
}
