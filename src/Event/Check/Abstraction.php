<?php

namespace phpbu\App\Event\Check;

use phpbu\App\Configuration\Backup\Check;
use phpbu\App\Event\Action;

/**
 * Check event base class.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Abstraction extends Action
{
    /**
     * Constructor
     *
     * @param \phpbu\App\Configuration\Backup\Check $check
     */
    public function __construct(Check $check)
    {
        $this->configuration = $check;
    }
}
