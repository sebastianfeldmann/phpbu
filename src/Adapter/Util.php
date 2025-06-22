<?php

namespace phpbu\App\Adapter;

/**
 * Adapter utility class
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.7
 */
abstract class Util
{
    /**
     * Finds all adapter references in a value string
     *
     * This returns a list of all found adapter references in an array like:
     *   [
     *     [
     *       'search'  => 'adapter:name:path',
     *       'adapter' => 'name',
     *       'path'    => 'path'
     *     ],
     *     ...
     *   ]
     *
     * @param  string $value
     * @return array
     */
    public static function getAdapterReplacements(string $value) : array
    {
        $values  = [];
        $matches = [];
        if (preg_match_all('#:?adapter:([a-z0-9_\-]+):([^:]+):?#i', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $values[] = [
                    'search'  => $match[0],
                    'adapter' => $match[1],
                    'path'    => $match[2]
                ];
            }
        }
        return $values;
    }
}
