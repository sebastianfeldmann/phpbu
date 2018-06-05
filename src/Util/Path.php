<?php
namespace phpbu\App\Util;

/**
 * Path utility class.
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Path
{
    /**
     * Date placeholder replacement.
     * Replaces %{somevalue} with date({somevalue}).
     *
     * @param  string               $string
     * @param  mixed <integer|null> $time
     * @return string
     */
    public static function replaceDatePlaceholders(string $string, $time = null) : string
    {
        $time = $time === null ? time() : $time;
        return preg_replace_callback(
            '#%([a-zA-Z])#',
            function($match) use ($time) {
                return date($match[1], $time);
            },
            $string
        );
    }

    /**
     * Does a given string contain a date placeholder.
     *
     * @param  string $string
     * @return bool
     */
    public static function isContainingPlaceholder(string $string) : bool
    {
        return false !== strpos($string, '%');
    }

    /**
     * Replaces %TARGET_DIR% and %TARGET_FILE% in given string.
     *
     * @param  string $string
     * @param  string $target
     * @return string
     */
    public static function replaceTargetPlaceholders(string $string, string $target) : string
    {
        $targetDir  = dirname($target);
        $search     = ['%TARGET_DIR%', '%TARGET_FILE%'];
        $replace    = [$targetDir, $target];
        return str_replace($search, $replace, $string);
    }

    /**
     * Create a regex that matches the raw path considering possible date placeholders.
     *
     * @param  string $stringWithDatePlaceholders
     * @return string
     */
    public static function datePlaceholdersToRegex(string $stringWithDatePlaceholders) : string
    {
        $dateRegex = [
            '%Y' => '[0-9]{4}',
            '%y' => '[0-9]{2}',
            '%m' => '[0-9]{2}',
            '%M' => '[a-z]{3}',
            '%d' => '[0-9]{2}',
            '%D' => '[a-z]{3}',
            '%j' => '[1-9]{1,2}',
            '%H' => '[0-9]{2}',
            '%s' => '[0-9]{2}',
            '%i' => '[0-9]{2}',
            '%N' => '[1-7]{1}',
            '%w' => '[0-6]{1}',
            '%z' => '[0-9]{1,3}',
        ];
        $regex = preg_quote($stringWithDatePlaceholders, '#');
        $regex = str_replace(array_keys($dateRegex), array_values($dateRegex), $regex);
        return preg_replace('#%[a-z]#i', '[0-9a-z]+', $regex);
    }

    /**
     * Determine if the path has a trailing slash.
     *
     * @param  string $string
     * @return bool
     */
    public static function hasTrailingSlash(string $string) : bool
    {
        return substr($string, -1) === '/';
    }

    /**
     * Adds trailing slash to a string/path if not already there.
     *
     * @param  string $string
     * @return string
     */
    public static function withTrailingSlash(string $string) : string
    {
        return $string . (self::hasTrailingSlash($string) ? '' : '/');
    }

    /**
     * Removes the trailing slash from a string/path.
     *
     * @param  string $string
     * @return string
     */
    public static function withoutTrailingSlash(string $string) : string
    {
        return strlen($string) > 1 && self::hasTrailingSlash($string) ? substr($string, 0, -1) : $string;
    }

    /**
     * Determine if the path has a leading slash.
     *
     * @param  string $string
     * @return bool
     */
    public static function hasLeadingSlash(string $string) : bool
    {
        return substr($string, 0, 1) === '/';
    }

    /**
     * Adds leading slash to a string/path if not already there.
     *
     * @param  string $string
     * @return string
     */
    public static function withLeadingSlash(string $string) : string
    {
        return (self::hasLeadingSlash($string) ? '' : '/') . $string;
    }

    /**
     * Removes the leading slash from a string/path.
     *
     * @param  string $string
     * @return string
     */
    public static function withoutLeadingSlash(string $string) : string
    {
        return ltrim($string, '/');
    }

    /**
     * Removes trailing and leading sl
     * @param string $string
     * @return string
     */
    public static function withoutLeadingOrTrailingSlash(string $string) : string
    {
        return trim($string, '/');
    }

    /**
     * Is given path absolute.
     *
     * @param  string $path
     * @return bool
     */
    public static function isAbsolutePath(string $path) : bool
    {
        // path already absolute?
        if ($path[0] === '/') {
            return true;
        }

        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (defined('PHP_WINDOWS_VERSION_BUILD') && self::isAbsoluteWindowsPath($path)) {
            return true;
        }

        // Stream
        if (strpos($path, '://') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Is given path an absolute windows path.
     *
     * @param  string $path
     * @return bool
     */
    public static function isAbsoluteWindowsPath(string $path) : bool
    {
        return ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]\:[/\\\]#i', substr($path, 0, 3))));
    }

    /**
     * Converts a path to an absolute one if necessary relative to a given base path.
     *
     * @param  string  $path
     * @param  string  $base
     * @param  boolean $useIncludePath
     * @return string
     */
    public static function toAbsolutePath(string $path, string $base, bool $useIncludePath = false) : string
    {
        if (self::isAbsolutePath($path)) {
            return $path;
        }

        $file = $base . DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !file_exists($file)) {
            $includePathFile = stream_resolve_include_path($path);
            if ($includePathFile) {
                $file = $includePathFile;
            }
        }
        return $file;
    }

    /**
     * Return list of directories in a given path without absolute root element
     *
     * @param  string $path
     * @return array
     */
    public static function getDirectoryListFromPath(string $path) : array
    {
        $path = trim($path, '/');
        $dirs = explode('/', $path);
        return array_filter($dirs);
    }

    /**
     * Return list of directories in a given path with absolute root element.
     *
     * @param  string $path
     * @return array
     */
    public static function getDirectoryListFromAbsolutePath(string $path) : array
    {
        $dirs = static::getDirectoryListFromPath($path);
        if (self::hasLeadingSlash($path)) {
            array_unshift($dirs, '/');
        }
        return $dirs;
    }

    /**
     * Returns directory depth of a given path.
     *
     * @param  string $path
     * @return int
     */
    public static function getPathDepth(string $path) : int
    {
        return count(self::getDirectoryListFromAbsolutePath($path));
    }
}
