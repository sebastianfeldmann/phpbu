<?php
namespace phpbu\App\Event\Crypt;

use phpbu\App\Configuration\Backup\Crypt;
use phpbu\App\Event\Action;

/**
 * Check Event base class.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Abstraction extends Action
{
    /**
     * Constructor.
     *
     * @param \phpbu\App\Configuration\Backup\Crypt $crypt
     */
    public function __construct(Crypt $crypt)
    {
        $this->configuration = $crypt;
    }
}
