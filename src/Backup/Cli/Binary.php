<?php
namespace phpbu\App\Backup\Cli;

use phpbu\App\Backup\Compressor;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Cli;

/**
 * Execute Binary
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.3.0
 */
abstract class Binary
{
    /**
     * Path to command
     *
     * @var string
     */
    protected $binary;

    /**
     * Command to execute
     *
     * @var \phpbu\App\Backup\Cli\Exec
     */
    protected $exec;

    /**
     * Optional command locations
     *
     * @var array
     */
    private static $optionalCommandLocations = array(
        'mongodump' => array(),
        'mysqldump' => array(
            '/usr/local/mysql/bin/mysqldump', // Mac OS X
            '/usr/mysql/bin/mysqldump',       // Linux
        ),
        'tar'       => array(),
    );

    /**
     * @param  string $cmd
     * @param  string $path
     * @return string
     */
    protected function detectCommand($cmd, $path = null)
    {
        return Cli::detectCmdLocation($cmd, $path, self::getCommandLocations($cmd));
    }

    /**
     * Executes the cli commands and handles compression
     *
     * @param  \phpbu\App\Backup\Cli\Exec   $exec
     * @param  string                       $redirect
     * @param  \phpbu\App\Backup\Compressor $compressor
     * @return \phpbu\App\Backup\Cli\Result
     * @throws \phpbu\App\Exception
     */
    protected function execute(Exec $exec, $redirect = null, Compressor $compressor = null)
    {
        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res    = $exec->execute($redirect);
        $code   = $res->getCode();
        $cmd    = $res->getCmd();
        $output = $res->getOutput();

        if ($code == 0) {
            // run the compressor command
            if (null !== $compressor) {
                // compress the generated output with configured compressor
                $res = $this->compressOutput($redirect, $compressor);

                if ($res->getCode() !== 0) {
                    // remove compressed file with errors
                    $this->unlinkErrorFile($redirect . '.' . $compressor->getSuffix());
                }

                $cmd   .= PHP_EOL . $res->getCmd();
                $code  += $res->getCode();
                $output = array_merge($output, $res->getOutput());
            }
        } else {
            // remove file with errors
            $this->unlinkErrorFile($redirect);
        }

        return new Result($cmd, $code, $output);
    }

    /**
     * Compress the generated output.
     *
     * @param  string $file
     * @param  \phpbu\App\Backup\Compressor
     * @return \phpbu\App\Backup\Cli\Result
     */
    protected function compressOutput($file, $compressor)
    {
        $exec = $compressor->getExec($file, array('-f'));

        $old = error_reporting(0);
        $res = $exec->execute();
        error_reporting($old);

        return $res;
    }

    /**
     * Binary setter, mostly for test purposes.
     *
     * @param string $pathToMysqldump
     */
    public function setBinary($pathToMysqldump)
    {
        $this->binary = $pathToMysqldump;
    }

    /**
     * Exec setter, mostly for test purposes.
     *
     * @param \phpbu\App\Backup\Cli\Exec $exec
     */
    public function setExec(Exec $exec)
    {
        $this->exec = $exec;
    }

    /**
     * Adds an option to a command if it is not empty.
     *
     * @param \phpbu\App\Backup\Cli\Cmd $cmd
     * @param string  $option
     * @param mixed   $check
     * @param boolean $asValue
     * @param string  $glue
     */
    protected function addOptionIfNotEmpty(Cmd $cmd, $option, $check, $asValue = true, $glue = '=')
    {
        if (!empty($check)) {
            if ($asValue) {
                $cmd->addOption($option, $check, $glue);
            } else {
                $cmd->addOption($option);
            }
        }
    }

    /**
     * Replaces %TARGET_DIR% and %TARGET_FILE% in given string.
     *
     * @param  string $string
     * @param  Target $target
     * @return string
     */
    protected function replaceTargetPlaceholder($string, Target $target)
    {
        $targetFile = $target->getPathname();
        $targetDir  = dirname($targetFile);
        $search     = array('%TARGET_DIR%', '%TARGET_FILE%');
        $replace    = array($targetDir, $targetFile);
        return str_replace($search, $replace, $string);
    }

    /**
     * Remove file if it exists.
     *
     * @param string $file
     */
    protected function unlinkErrorFile($file)
    {
        if (file_exists($file) && !is_dir($file)) {
            unlink($file);
        }
    }

    /**
     * Adds a new 'path' to the list of optional command locations.
     *
     * @param string $command
     * @param string $path
     */
    public static function addCommandLocation($command, $path)
    {
        self::$optionalCommandLocations[$command][] = $path;
    }

    /**
     * Returns the list of optional 'mysqldump' locations.
     *
     * @param  string $command
     * @return array
     */
    public static function getCommandLocations($command)
    {
        return isset(self::$optionalCommandLocations[$command]) ? self::$optionalCommandLocations[$command] : array();
    }
}
