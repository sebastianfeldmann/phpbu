<?php
namespace phpbu\App\Util;

use RuntimeException;

/**
 * Cli utility
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
abstract class Cli
{
    /**
     * List of paths
     *
     * @var array
     */
    private static $basePaths = array();

    /**
     * Register a base path.
     *
     * @param  string $name
     * @param  string $path
     * @throws \RuntimeException
     */
    public static function registerBase($name, $path)
    {
        if (!self::isAbsolutePath($path)) {
            throw new RuntimeException(sprintf('path has to be absolute: %s', $path));
        }
        self::$basePaths[$name] = $path;
    }

    /**
     * Retrieve a registered path.
     *
     * @param  string $name
     * @return string array
     * @throws \RuntimeException
     */
    public static function getBase($name)
    {
        if (!isset(self::$basePaths[$name])) {
            throw new RuntimeException(sprintf('base not registered: %s', $name));
        }
        return self::$basePaths[$name];
    }

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
            $bin     = self::isExecutable($command);
            if (null === $bin) {
                throw new RuntimeException(sprintf('wrong path specified for \'%s\': %s', $cmd, $path));
            }
            return $bin;
        }

        // on nx systems use 'which' command.
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $command = `which $cmd`;
            $bin     = self::isExecutable($command);
            if (null !== $bin) {
                return $bin;
            }
        }

        // checking environment variable.
        $pathList = explode(PATH_SEPARATOR, self::getEnvPath());
        foreach ($pathList as $path) {
            $command = $path . DIRECTORY_SEPARATOR . $cmd;
            $bin     = self::isExecutable($command);
            if (null !== $bin) {
                return $bin;
            }
        }

        // some more paths we came across that where added manually
        foreach ($optionalLocations as $path) {
            $command = $path . DIRECTORY_SEPARATOR . $cmd;
            $bin     = self::isExecutable($command);
            if (null !== $bin) {
                return $bin;
            }
        }
        throw new RuntimeException(sprintf('\'%s\' was nowhere to be found please specify the correct path', $cmd));
    }

    /**
     * Return local $PATH variable.
     *
     * @return string
     * @throws \RuntimeException
     */
    public static function getEnvPath()
    {
        // check for unix and windows case $_SERVER index
        foreach (array('PATH', 'Path', 'path') as $index) {
            if (isset($_SERVER[$index])) {
                return $_SERVER[$index];
            }
        }
        throw new RuntimeException('cant find local PATH variable');
    }

    /**
     * Returns the executable command if the command is executable, null otherwise.
     * Search for $command.exe on Windows systems.
     *
     * @param  string $command
     * @return string
     */
    public static function isExecutable($command)
    {
        if (is_executable($command)) {
            return $command;
        }
        // on windows check the .exe suffix
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $command .= '.exe';
            if (is_executable($command)) {
                return $command;
            }
        }
        return null;
    }

    /**
     * Is given path absolute.
     *
     * @param  string $path
     * @return boolean
     */
    public static function isAbsolutePath($path)
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
    public static function isAbsoluteWindowsPath($path)
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
    public static function toAbsolutePath($path, $base, $useIncludePath = false)
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
     * Removes a directory that is not empty.
     *
     * @param $dir
     */
    public static function removeDir($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir($dir . '/' . $file)) {
                self::removeDir($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file);
            }
        }
        rmdir($dir);
    }
}
