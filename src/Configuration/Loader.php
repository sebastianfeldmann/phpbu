<?php
namespace phpbu\App\Configuration;

/**
 * Configuration loader interface.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
