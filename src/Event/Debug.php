<?php
namespace phpbu\App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Debug Event
 *
 * @package    phpbu
 * @subpackage Event
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Debug extends Event
{
    /**
     * Event name
     */
    const NAME = 'phpbu.debug';

    /**
     * Debug message
     *
     * @var string
     */
    protected $message;

    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Message getter.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
