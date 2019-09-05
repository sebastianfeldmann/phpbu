<?php
namespace phpbu\App\Log;

use phpbu\App\Event\Subscriber;

/**
 * Cleanup
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Logger extends Subscriber
{
    /**
     * Setup the logger.
     *
     * @param array $options
     */
    public function setup(array $options);
}
