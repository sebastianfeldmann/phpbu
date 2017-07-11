<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Log\ResultFormatter;
use phpbu\App\Result;

/**
 * FormData ResultFormatter
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class FormData extends Abstraction implements ResultFormatter
{
    /**
     * Create request body from phpbu result data.
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    public function format(Result $result): string
    {
        $body = $this->getSummaryData($result);
        $body['errorMessages'] = $this->combineErrorMessages($result->getErrors());
        return http_build_query($body);
    }

    /**
     * Combine all error messages.
     *
     * @param  array $errors
     * @return string
     */
    private function combineErrorMessages(array $errors) : string
    {
        $messages = [];
        /** @var \Exception $e */
        foreach ($errors as $e) {
            $messages[] = $e->getMessage() . ' in file:' . $e->getFile() . ' at line' . $e->getLine();
        }
        return implode(';', $messages);
    }
}
