<?php
namespace phpbu\App\Event\App;

use phpbu\App\Configuration;
use phpbu\App\Event\Action;

/**
 * App event base class
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
     * @param \phpbu\App\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
}
