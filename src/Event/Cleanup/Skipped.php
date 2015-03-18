<?php
namespace phpbu\App\Event\Cleanup;

use phpbu\App\Event\Abstraction;

/**
 * Skipped Event
 *
 * @package    phpbu
 * @subpackage Event
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Skipped extends Abstraction
{
    const NAME = 'phpbu.cleanup_skipped';
}
