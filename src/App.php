<?php
/**
 * phpbu
 *
 * Copyright (c) 2014 - 2015, Sebastian Feldmann <sebastian@phpbu.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  1. Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  2. Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  3. Neither the name of the copyright holder nor the names of its
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
namespace phpbu;

use Phar;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Runner;
use phpbu\App\Version;

/**
 * Main application class.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class App
{
    const EXIT_SUCCESS   = 0;
    const EXIT_FAILURE   = 1;
    const EXIT_EXCEPTION = 2;

    /**
     * Ascii-Art app logo
     *
     * @var string
     */
    private static $logo = '             __          __
      ____  / /_  ____  / /_  __  __
     / __ \/ __ \/ __ \/ __ \/ / / /
    / /_/ / / / / /_/ / /_/ / /_/ /
   / .___/_/ /_/ .___/_.___/\__,_/
  /_/         /_/
';

    /**
     * Is version string printed already.
     *
     * @var boolean
     */
    private $isVersionStringPrinted = false;

    /**
     * List of given arguments
     *
     * @var array
     */
    private $arguments;

    /**
     * Runs the application.
     *
     * @param array $args
     */
    public function run(array $args)
    {
        $this->handleOpt($args);

        $ret    = self::EXIT_FAILURE;
        $runner = new Runner();

        try {
            $result = $runner->run($this->arguments);

            if ($result->wasSuccessful()) {
                $ret = self::EXIT_SUCCESS;
            } elseif ($result->errorCount() > 0) {
                $ret = self::EXIT_EXCEPTION;
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            $ret = self::EXIT_EXCEPTION;
        }
        
        exit($ret);
    }

    /**
     * Check arguments and load configuration file.
     *
     * @param array $args
     */
    protected function handleOpt(array $args)
    {
        try {
            $parser  = new App\Args();
            $options = $parser->getOptions($args);
            $this->handleArgs($options);
        } catch (Exception $e) {
            $this->printError($e->getMessage(), true);
        }

        if (isset($this->arguments['include-path'])) {
            $this->handleIncludePath($this->arguments['include-path']);
        }

        try {
            $this->handleConfiguration();
        } catch (Exception $e) {
            $this->printError($e->getMessage());
        }

        // no backups to handle
        if (!isset($this->arguments['backups'])) {
            $this->printLogo();
            $this->printHelp();
            exit(self::EXIT_EXCEPTION);
        }
    }

    /**
     * Handle the parsed command line options
     * 
     * @param  array $options
     * @return void
     */
    protected function handleArgs(array $options)
    {
        foreach ($options as $option => $argument) {
            switch ($option) {
                case '--bootstrap':
                    $this->arguments['bootstrap'] = $argument;
                    break;
                case '--colors':
                    $this->arguments['colors'] = $argument;
                    break;
                case '--configuration':
                    $this->arguments['configuration'] = $argument;
                    break;
                case '--debug':
                    $this->arguments['debug'] = $argument;
                    break;
                case '-h':
                case '--help':
                    $this->printHelp();
                    exit(self::EXIT_SUCCESS);
                case 'include-path':
                    $this->arguments['include-path'] = $argument;
                    break;
                case '--selfupdate':
                case '--self-update':
                    $this->handleSelfUpdate();
                    break;
                case '-v':
                case '--verbose':
                    $this->arguments['verbose'] = true;
                    break;
                case '-V':
                case '--version':
                    $this->printVersionString();
                    exit(self::EXIT_SUCCESS);
            }
        }
    }

    /**
     * Check all configuration possibilities.
     * 
     * @return void
     */
    protected function handleConfiguration()
    {
        // check configuration argument
        // if configuration argument is a directory check for default configuration files
        // phpbu.xml, phpbu.xml.dist
        if (isset($this->arguments['configuration']) && is_dir($this->arguments['configuration'])) {
            $this->handleConfigurationDir();
        } elseif (!isset($this->arguments['configuration'])) {
            // no configuration argument search for default configuration files
            // phpbu.xml, phpbu.xml.dist in current working directory
            $this->handleConfigurationDefault();
        }
        if (isset($this->arguments['configuration'])) {
            $configuration = new Configuration($this->arguments['configuration']);

            $phpbu                      = $configuration->getAppSettings();
            $phpSettings                = $configuration->getPhpSettings();
            $this->arguments['logging'] = $configuration->getLoggingSettings();
            $this->arguments['backups'] = $configuration->getBackupSettings();

            // argument bootstrap overrules config bootstrap
            if (isset($this->arguments['bootstrap'])) {
                $this->handleBootstrap($this->arguments['bootstrap']);
            } elseif (isset($phpbu['bootstrap'])) {
                $this->handleBootstrap($phpbu['bootstrap']);
            }

            if (isset($phpbu['verbose']) && $phpbu['verbose'] === true) {
                $this->arguments['verbose'] = true;
            }

            if (isset($phpbu['colors']) && $phpbu['colors'] === true) {
                $this->arguments['colors'] = true;
            }

            if (isset($phpbu['debug']) && $phpbu['debug'] === true) {
                $this->arguments['debug'] = true;
            }

            if (!empty($phpSettings['include_path'])) {
                $this->handleIncludePath($phpSettings['include_path']);
            }

            // handle php.ini settings
            foreach ($phpSettings['ini'] as $name => $value) {
                if (defined($value)) {
                    $value = constant($value);
                }
                ini_set($name, $value);
            }
        }
    }

    /**
     * Check directory for default configuration files phpbu.xml, phpbu.xml.dist.
     * 
     * @return void
     */
    protected function handleConfigurationDir()
    {
        $configurationFile = $this->arguments['configuration'] . '/phpbu.xml';

        if (file_exists($configurationFile)) {
            $this->arguments['configuration'] = realpath($configurationFile);
        } elseif (file_exists($configurationFile . '.dist')) {
            $this->arguments['configuration'] = realpath($configurationFile . '.dist');
        }
    }

    /**
     * Check default configuration files phpbu.xml, phpbu.xml.dist in current working directory.
     * 
     * @return void
     */
    protected function handleConfigurationDefault()
    {
        if (file_exists('phpbu.xml')) {
            $this->arguments['configuration'] = realpath('phpbu.xml');
        } elseif (file_exists('phpbu.xml.dist')) {
            $this->arguments['configuration'] = realpath('phpbu.xml.dist');
        }
    }

    /**
     * Handles the php include_path settings.
     *
     * @param  mixed $path
     * @return void
     */
    protected function handleIncludePath($path)
    {
        if (is_array($path)) {
            $path = implode(PATH_SEPARATOR, $path);
        }

        ini_set('include_path', $path . PATH_SEPARATOR . ini_get('include_path'));
    }

    /**
     * Handles the bootstrap file inclusion.
     *
     * @param  string $filename
     * @throws \phpbu\App\Exception
     */
    protected function handleBootstrap($filename)
    {
        $pathToFile = stream_resolve_include_path($filename);

        if (!$pathToFile || !is_readable($pathToFile)) {
            throw new Exception(sprintf('Cannot open bootstrap file "%s".' . PHP_EOL, $filename));
        }

        require $pathToFile;
    }

    /**
     * Handle the phar self-update.
     */
    protected function handleSelfUpdate()
    {
        $this->printVersionString();

        $remoteFilename = sprintf('http://phar.phpbu.de/phpbu%s.phar', Version::getReleaseChannel());
        $localFilename  = realpath($_SERVER['argv'][0]);
        $tempFilename   = basename($localFilename, '.phar') . '-temp.phar';

        echo 'Updating the phpbu PHAR ... ';

        $old  = error_reporting(0);
        $phar = file_get_contents($remoteFilename);
        error_reporting($old);
        if (!$phar) {
            echo ' failed' . PHP_EOL . 'Could not reach phpbu update site' . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }
        file_put_contents($tempFilename, $phar);

        chmod($tempFilename, 0777 & ~umask());

        // check downloaded phar
        try {
            $phar = new Phar($tempFilename);
            unset($phar);
            // replace current phar with the new one
            rename($tempFilename, $localFilename);
        } catch (Exception $e) {
            // cleanup crappy phar
            unlink($tempFilename);
            echo 'failed' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }

        echo 'done' . PHP_EOL;
        exit(self::EXIT_SUCCESS);
    }

    /**
     * Shows the current application version.
     */
    private function printVersionString()
    {
        if ($this->isVersionStringPrinted) {
            return;
        }

        echo Version::getVersionString() . PHP_EOL;
        $this->isVersionStringPrinted = true;
    }

    /**
     * Show the phpbu logo
     */
    protected function printLogo()
    {
        echo self::$logo . PHP_EOL;
    }

    /**
     * Show the help message.
     */
    protected function printHelp()
    {
        $this->printVersionString();
        echo <<<EOT

Usage: phpbu [option]

  --bootstrap=<file>     A "bootstrap" PHP file that is included before the backup.
  --configuration=<file> A phpbu xml config file.
  --colors               Use colors in output.
  --debug                Display debugging information during backup generation.
  -h, --help             Prints this usage information.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.

EOT;
        if (defined('__PHPBU_PHAR__')) {
            echo '  --self-update          Update phpbu to the latest version.' . PHP_EOL;
        }
    }

    /**
     * Shows some given error message.
     *
     * @param string $message
     * @param bool   $hint
     */
    private function printError($message, $hint = false)
    {
        $help = $hint ? ', use "phpbu -h" for help' : '';
        $this->printVersionString();
        echo $message . $help . PHP_EOL;
        exit(self::EXIT_EXCEPTION);
    }

    /**
     * Main method, is called by phpbu command and the phar file.
     */
    public static function main()
    {
        $app = new static();
        $app->run($_SERVER['argv']);
    }
}
