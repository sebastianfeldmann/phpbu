<?php
namespace phpbu\Util;

use RuntimeException;

/**
 * Cli utility
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
abstract class Cli
{
    /**
     * Detect a given commands location.
     *
     * @param  string $cmd               The command to be located
     * @param  string $path              Directory where the command should be located
     * @param  array  $optionalLocations Some fallback locations where to investigate
     * @return string                    Absolute path to detected command including command itself
     * @throws \RuntimeException
     */
    public static function detectCmdLocation($cmd, $path = null, $optionalLocations = array())
    {
        // explicit path given, so check it out
        if (null !== $path) {
            $command = $path . DIRECTORY_SEPARATOR . $cmd;
            if (!is_executable($command)) {
                throw new RuntimeException(sprintf('wrong path specified for \'%s\': %s', $cmd, $path));
            }
            return $command;
        }

        // on nx systems use 'which' command.
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $command = `which $cmd`;
            if (is_executable($command)) {
                return $command;
            }
        }

        // checking environment variable.
        $pathList = explode(PATH_SEPARATOR, $_SERVER['PATH']);
        foreach ($pathList as $path) {
            $command = $path . DIRECTORY_SEPARATOR . $cmd;
            if (is_executable($command)) {
                return $command;
            }
        }

        // some more pathes we came accross that where added manualy
        foreach ($optionalLocations as $path) {
            $command = $path . DIRECTORY_SEPARATOR . $cmd;
            if (is_executable($command)) {
                return $command;
            }
        }
        throw new RuntimeException(sprintf('\'%s\' was nowhere to be found please specify the correct path', $cmd));
    }
}
