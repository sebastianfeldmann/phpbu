<?php
/**
 * phpbu
 *
 * Copyright (c) 2014 - 2016 Sebastian Feldmann <sebastian@phpbu.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
namespace phpbu\App;

use Phar;
use phpbu\App\Cmd\Args;
use phpbu\App\Util\Arr;

/**
 * Main application class.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Cmd
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
     * Is cmd executed from phar.
     *
     * @var boolean
     */
    private $isPhar;

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
        $this->isPhar = defined('__PHPBU_PHAR__');
        $this->handleOpt($args);
        $this->findConfiguration();

        $ret    = self::EXIT_FAILURE;
        $runner = new Runner(new Factory());

        try {
            $result = $runner->run($this->createConfiguration());

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
            $parser  = new Args($this->isPhar);
            $options = $parser->getOptions($args);
            $this->handleArgs($options);
        } catch (Exception $e) {
            $this->printError($e->getMessage(), true);
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
                case '--simulate':
                    $this->arguments['simulate'] = $argument;
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
     * Try to find the configuration file.
     */
    protected function findConfiguration()
    {
        // check configuration argument
        // if configuration argument is a directory
        // check for default configuration files 'phpbu.xml' and 'phpbu.xml.dist'
        if (isset($this->arguments['configuration']) && is_file($this->arguments['configuration'])) {
            $this->arguments['configuration'] = realpath($this->arguments['configuration']);
        } elseif (isset($this->arguments['configuration']) && is_dir($this->arguments['configuration'])) {
            $this->findConfigurationInDir();
        } elseif (!isset($this->arguments['configuration'])) {
            // no configuration argument search for default configuration files
            // 'phpbu.xml' and 'phpbu.xml.dist' in current working directory
            $this->findConfigurationDefault();
        }
        // no config found, exit with some help output
        if (!isset($this->arguments['configuration'])) {
            $this->printLogo();
            $this->printHelp();
            exit(self::EXIT_EXCEPTION);
        }
    }

    /**
     * Check directory for default configuration files phpbu.xml, phpbu.xml.dist.
     *
     * @return void
     */
    protected function findConfigurationInDir()
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
    protected function findConfigurationDefault()
    {
        if (file_exists('phpbu.xml')) {
            $this->arguments['configuration'] = realpath('phpbu.xml');
        } elseif (file_exists('phpbu.xml.dist')) {
            $this->arguments['configuration'] = realpath('phpbu.xml.dist');
        }
    }

    /**
     * Create a application configuration.
     *
     * @return \phpbu\App\Configuration
     */
    protected function createConfiguration()
    {
        $configLoader  = Configuration\Loader\Factory::createLoader($this->arguments['configuration']);
        $configuration = $configLoader->getConfiguration();

        // command line arguments overrule the config file settings
        $this->overrideConfigWithArgument($configuration, 'verbose');
        $this->overrideConfigWithArgument($configuration, 'colors');
        $this->overrideConfigWithArgument($configuration, 'debug');
        $this->overrideConfigWithArgument($configuration, 'simulate');

        // check for command line bootstrap option
        $bootstrap = Arr::getValue($this->arguments, 'bootstrap');
        if (!empty($bootstrap)) {
            $configuration->setBootstrap($bootstrap);
        }

        // add a cli printer for some output
        $configuration->addLogger(
            new Result\PrinterCli(
                $configuration->getVerbose(),
                $configuration->getColors(),
                ($configuration->getDebug() || $configuration->isSimulation())
            )
        );
        return $configuration;
    }

    /**
     * Override configuration settings with command line arguments.
     *
     * @param \phpbu\App\Configuration $configuration
     * @param string                   $value
     */
    protected function overrideConfigWithArgument(Configuration $configuration, $value)
    {
        if (Arr::getValue($this->arguments, $value) === true) {
            $setter = 'set' . ucfirst($value);
            $configuration->{$setter}(true);
        }
    }

    /**
     * Handle the phar self-update.
     */
    protected function handleSelfUpdate()
    {
        $this->printVersionString();

        $remoteFilename = 'http://phar.phpbu.de/phpbu.phar';
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
  --simulate             Show what phpbu would do without actually executing anything.
  -h, --help             Print this usage information.
  -v, --verbose          Output more verbose information.
  -V, --version          Output version information and exit.

EOT;
        if ($this->isPhar) {
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
