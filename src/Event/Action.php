<?php
namespace phpbu\App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * phpbu event base class
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Action extends Event
{
    /**
     * Application configuration.
     *
     * @var mixed
     */
    protected $configuration;

    /**
     * Configuration getter.
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
