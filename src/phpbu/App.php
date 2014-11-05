<?php
/**
 * phpbu
 *
 * Copyright (c) 2014, Sebastian Feldmann <sebastian@phpbu.de>
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
 * @copyright  2014 Sebastian Feldmann
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
namespace phpbu;

use Phar;
use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Version;

/**
 * Main application class.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
    private static $logo = '         __              __
        /\ \            /\ \
   _____\ \ \___   _____\ \ \____  __  __
  /\ \'__`\ \  _ `\/\ \'__`\ \ \'__`\/\ \/\ \
  \ \ \L\ \ \ \ \ \ \ \L\ \ \ \L\ \ \ \_\ \
   \ \ ,__/\ \_\ \_\ \ ,__/\ \_,__/\ \____/
    \ \ \/  \/_/\/_/\ \ \/  \/___/  \/___/
     \ \_\           \ \_\
      \/_/            \/_/
';

    /**
     * Is version string printed already.
     *
     * @var boolean
     */
    private $isVersionStringPrinted = false;

    /**
     * Config.
     *
     * @var string
     */
    private $configuration;

    /**
     * List of given arguments
     *
     * @var array
     */
    private $arguments;

    /**
     * Runs the application
     *
     * @param array $args
     */
    public function run(array $args)
    {
        $this->handleOpt($args);

        // create logger

        // create backups

        // do sanity checks

        // do syncs
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
        } catch (Exception $e) {
            $this->printError($e->getMessage(), true);
        }

        foreach ($options as $option => $argument) {
            switch ($option) {
                case '--bootstrap':
                    $this->arguments['bootstrap'] = $argument;
                    break;
                case '--configuration':
                    $this->arguments['configuration'] = $argument;
                    break;
                case '-h':
                case '--help':
                    $this->printHelp();
                    exit(self::EXIT_SUCCESS);
                    break;
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
                    break;
            }
        }

        if (isset($this->arguments['include-path'])) {
            $this->handleIncludePath($this->arguments['include-path']);
        }

        // check configuration argument
        // if configuration argument is a directory check for default configuration files
        // phpbu.xml, phpbu.xml.dist
        if (isset($this->arguments['configuration']) && is_dir($this->arguments['configuration'])) {
            $configurationFile = $this->arguments['configuration'] . '/phpbu.xml';

            if (file_exists($configurationFile)) {
                $this->arguments['configuration'] = realpath($configurationFile);
            } elseif (file_exists($configurationFile . '.dist')) {
                $this->arguments['configuration'] = realpath($configurationFile . '.dist');
            }
        } elseif (!isset($this->arguments['configuration'])) {
            // no configuration argument search for default configuration files
            // phpbu.xml, phpbu.xml.dist in current working directory
            if (file_exists('phpbu.xml')) {
                $this->arguments['configuration'] = realpath('phpbu.xml');
            } elseif (file_exists('phpbu.xml.dist')) {
                $this->arguments['configuration'] = realpath('phpbu.xml.dist');
            }
        }

        if (isset($this->arguments['configuration'])) {
            try {
                $configuration = new Configuration($this->arguments['configuration']);
            } catch (Exception $e) {
                $this->printError($e->getMessage());
                exit(self::EXIT_FAILURE);
            }

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
            // elseif so we don't bootstrap twice by accident
        } elseif (isset($this->arguments['bootstrap'])) {
            $this->handleBootstrap($this->arguments['bootstrap']);
        }

        // no backups to handle
        if (!isset($this->arguments['backups'])) {
            $this->printHelp();
            exit(self::EXIT_EXCEPTION);
        }
    }

    /**
     * Handles the php include_path settings.
     *
     * @param string|array $path
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
     * @throws phpbu\App\Exception
     */
    protected function handleBootstrap($filename)
    {
        $pathToFile = stream_resolve_include_path($filename);

        if (!$pathToFile || !is_readable($pathToFile)) {
            throw new Exception(sprintf('Cannot open bootstrap file "%s".' . "\n", $filename));
        }

        include_once $pathToFile;
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

        print 'Updating the phpbu PHAR ... ';

        file_put_contents($tempFilename, file_get_contents($remoteFilename));

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
            print ' failed' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }

        print ' done' . PHP_EOL;
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

        print Version::getVersionString() . PHP_EOL;
        $this->isVersionStringPrinted = true;
    }

    /**
     * Show the help message.
     */
    protected function printHelp()
    {
        $this->printVersionString();
        print <<<EOT

Usage: phpbu [option]

  --bootstrap=<file>     A "bootstrap" PHP file that is included before the backup.
  --configuration=<file> A phpbu xml config file.
  -h, --help             Display the help message and exit.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.

EOT;
        if (defined('__PHPBU_PHAR__')) {
            print '  --self-update          Update phpbu to the latest version.' . PHP_EOL;
        }
    }

    /**
     * Shows some given error message.
     *
     * @param string $message
     */
    private function printError($message, $hint = false)
    {
        $help = $hint ? ', use "phpbu -h" for help' : '';
        $this->printVersionString();
        print $message . $help . PHP_EOL;
        exit(self::EXIT_EXCEPTION);
    }

    /**
     * Main method, is called by phpbu command and the pahr file.
     *
     * @param boolean $exit
     */
    public static function main()
    {
        $app = new static();
        return $app->run($_SERVER['argv']);
    }
}
