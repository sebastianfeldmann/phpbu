<?php
namespace phpbu\App\Event;

/**
 * Debug Event
 *
 * @package    phpbu
 * @subpackage Event
 * @author     MoeBrowne <moebrowne@users.noreply.github.com>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 6.0.0
 */
class Warning
{
    /**
     * Event name
     */
    const NAME = 'phpbu.warning';

    /**
     * Warning message
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
