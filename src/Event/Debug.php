<?php
namespace phpbu\App\Event;

/**
 * Debug Event
 *
 * @package    phpbu
 * @subpackage Event
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Debug
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
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Message getter.
     *
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }
}
