<?php
namespace phpbu\App\Event;

/**
 * phpbu event base class
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Action
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
