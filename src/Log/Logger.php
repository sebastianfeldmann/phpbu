<?php
namespace phpbu\App\Log;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cleanup
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Logger extends EventSubscriberInterface
{
    /**
     * Setup the logger.
     *
     * @param array $options
     */
    public function setup(array $options);
}
