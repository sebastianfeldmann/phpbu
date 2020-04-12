<?php
namespace phpbu\App\Event\App;

use phpbu\App\Result;

/**
 * End Event
 *
 * @package    phpbu
 * @subpackage Event
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class End
{
    /**
     * Event name
     */
    const NAME = 'phpbu.app_end';

    /**
     * @var \phpbu\App\Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param \phpbu\App\Result $result
     */
    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    /**
     * Result getter.
     *
     * @return \phpbu\App\Result
     */
    public function getResult() : Result
    {
        return $this->result;
    }
}
