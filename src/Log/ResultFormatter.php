<?php
namespace phpbu\App\Log;

use phpbu\App\Result;

/**
 * Webhook Body ResultFormatter Interface
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
interface ResultFormatter
{
    /**
     * Create request body from phpbu result data.
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    public function format(Result $result) : string;
}
