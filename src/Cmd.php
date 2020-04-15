<?php
/**
 * phpbu
 *
 * Copyright (c) 2014 - 2017 Sebastian Feldmann <sebastian@phpbu.de>
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
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
namespace phpbu\App;

use Phar;
use phpbu\App\Cmd\Args;
use phpbu\App\Configuration\Bootstrapper;
use phpbu\App\Util\Arr;
use function fgets;
use function file_put_contents;
use function getcwd;
use function sprintf;
use function trim;
use const PHP_EOL;
use const STDIN;

/**
 * Main application class.
 *
 * @package    phpbu
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
final class Cmd
{
    const EXIT_SUCCESS   = 0;
    const EXIT_FAILURE   = 1;
    const EXIT_EXCEPTION = 2;

    /**
     * Is cmd executed from phar
     *
     * @var bool
     */
    private $isPhar;

    /**
     * Is version string printed already
     *
     * @var bool
     */
    private $isVersionStringPrinted = false;

    /**
     * List of given arguments
     *
     * @var array
     */
    private $arguments = [];

    /**
     * Runs the application
     *
     * @param array $args
     */
    public function run(array $args) : void
    {
        $this->isPhar = defined('__PHPBU_PHAR__');
        $this->handleOpt($args);

        $ret        = self::EXIT_FAILURE;
        $factory    = new Factory();
        $runner     = new Runner($factory);
        $configFile = $this->findConfiguration();

        try {
            $this->printVersionString();
            $result = $runner->run($this->createConfiguration($configFile, $factory));

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
     * Check arguments and load configuration file
     *
     * @param array $args
     */
    protected function handleOpt(array $args) : void
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
    protected function handleArgs(array $options) : void
    {
        foreach ($options as $option => $argument) {
            switch ($option) {
                case '-V':
                case '--version':
                    $this->printVersionString();
                    exit(self::EXIT_SUCCESS);
                case '--self-update':
                    $this->handleSelfUpdate();
                    exit(self::EXIT_SUCCESS);
                case '--generate-configuration':
                    $this->handleConfigGeneration();
                    exit(self::EXIT_SUCCESS);
                case '--version-check':
                    $this->handleVersionCheck();
                    exit(self::EXIT_SUCCESS);
                case '-h':
                case '--help':
                    $this->printHelp();
                    exit(self::EXIT_SUCCESS);
                case '-v':
                    $this->arguments['verbose'] = true;
                    break;
                default:
                    $this->arguments[trim($option, '-')] = $argument;
                    break;
            }
        }
    }

    /**
     * Try to find a configuration file
     */
    protected function findConfiguration() : string
    {
        $configOption = $this->arguments['configuration'] ?? '';

        try {
            $finder = new Configuration\Finder();
            return $finder->findConfiguration($configOption);
        } catch (\Exception $e) {
            // config option given but still no config found
            if (!empty($configOption)) {
                $this->printError('Can\'t find configuration file.');
            }
            $this->printHelp();
            exit(self::EXIT_EXCEPTION);
        }
    }

    /**
     * Create a application configuration
     *
     * @param  string             $configurationFile
     * @param  \phpbu\App\Factory $factory
     * @return \phpbu\App\Configuration
     * @throws \phpbu\App\Exception
     */
    protected function createConfiguration(string $configurationFile, Factory $factory) : Configuration
    {
        // setup bootstrapper with --bootstrap option that has precedence over the config file value
        $bootstrapper  = new Bootstrapper(Arr::getValue($this->arguments, 'bootstrap', ''));
        $configLoader  = Configuration\Loader\Factory::createLoader($configurationFile, $bootstrapper);

        if ($configLoader->hasValidationErrors()) {
            echo "  Warning - The configuration file did not pass validation!" . PHP_EOL .
                 "  The following problems have been detected:" . PHP_EOL;

            foreach ($configLoader->getValidationErrors() as $line => $errors) {
                echo sprintf("\n  Line %d:\n", $line);
                foreach ($errors as $msg) {
                    echo sprintf("  - %s\n", $msg);
                }
            }
            echo PHP_EOL;
        }

        $configuration = $configLoader->getConfiguration($factory);

        // command line arguments overrule the config file settings
        $this->overrideConfigWithArguments($configuration);

        // check for command line limit option
        $limitOption = Arr::getValue($this->arguments, 'limit');
        $configuration->setLimit(!empty($limitOption) ? explode(',', $limitOption) : []);

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
     * Override configuration settings with command line arguments
     *
     * @param \phpbu\App\Configuration $configuration
     */
    protected function overrideConfigWithArguments(Configuration $configuration) : void
    {
        $settingsToOverride = ['verbose', 'colors', 'debug', 'simulate', 'restore'];
        foreach ($settingsToOverride as $arg) {
            $value = Arr::getValue($this->arguments, $arg);
            if (!empty($value)) {
                $setter = 'set' . ucfirst($arg);
                $configuration->{$setter}($value);
            }
        }
    }

    /**
     * Handle the phar self-update
     */
    protected function handleSelfUpdate() : void
    {
        $this->printVersionString();

        // check if upgrade is necessary
        $latestVersion = $this->getLatestVersion();
        if (!$this->isPharOutdated($latestVersion)) {
            echo 'You already have the latest version of phpbu installed.' . PHP_EOL;
            exit(self::EXIT_SUCCESS);
        }

        $remoteFilename = 'http://phar.phpbu.de/phpbu.phar';
        $localFilename  = realpath($_SERVER['argv'][0]);
        $tempFilename   = basename($localFilename, '.phar') . '-temp.phar';

        echo 'Updating the phpbu PHAR to version ' . $latestVersion . ' ... ';

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
        } catch (\Exception $e) {
            // cleanup crappy phar
            unlink($tempFilename);
            echo 'failed' . PHP_EOL . $e->getMessage() . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }

        echo 'done' . PHP_EOL;
    }

    /**
     * Handle phar version-check
     */
    protected function handleVersionCheck() : void
    {
        $this->printVersionString();

        $latestVersion = $this->getLatestVersion();
        if ($this->isPharOutdated($latestVersion)) {
            print 'You are not using the latest version of phpbu.' . PHP_EOL
                . 'Use "phpbu --self-update" to install phpbu ' . $latestVersion . PHP_EOL;
        } else {
            print 'You are using the latest version of phpbu.' . PHP_EOL;
        }
    }

    /**
     * Create a configuration file by asking the user some questions
     *
     * @return void
     */
    private function handleConfigGeneration() : void
    {
        $this->printVersionString();

        print 'Configuration file format: xml|json (default: xml): ';
        $format = trim(fgets(STDIN)) === 'json' ? 'json' : 'xml';
        $file   = 'phpbu.' . $format;

        if (file_exists($file)) {
            echo '  FAILED: The configuration file already exists.' . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }

        print PHP_EOL . 'Generating ' . $file . ' in ' . getcwd() . PHP_EOL . PHP_EOL;

        print 'Bootstrap script (relative to path shown above; e.g: vendor/autoload.php): ';
        $bootstrapScript = trim(fgets(STDIN));

        $generator = new Configuration\Generator;

        file_put_contents(
            $file,
            $generator->generateConfigurationSkeleton(
                Version::minor(),
                $format,
                $bootstrapScript
            )
        );

        print PHP_EOL . 'Generated ' . $file . ' in ' . getcwd() . PHP_EOL . PHP_EOL .
            'ATTENTION:' . PHP_EOL .
            'The created configuration is just a skeleton. You have to finish the configuration manually.' . PHP_EOL;
    }

    /**
     * Returns latest released phpbu version
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getLatestVersion() : string
    {
        $old     = error_reporting(0);
        $version = file_get_contents('https://phar.phpbu.de/latest-version-of/phpbu');
        error_reporting($old);
        if (!$version) {
            echo 'Network-Error: Could not check latest version.' . PHP_EOL;
            exit(self::EXIT_EXCEPTION);
        }
        return $version;
    }

    /**
     * Check if current phar is outdated
     *
     * @param  string $latestVersion
     * @return bool
     */
    protected function isPharOutdated(string $latestVersion) : bool
    {
        return version_compare($latestVersion, Version::id(), '>');
    }

    /**
     * Shows the current application version.
     */
    protected function printVersionString() : void
    {
        if ($this->isVersionStringPrinted) {
            return;
        }

        echo Version::getVersionString() . PHP_EOL . PHP_EOL;
        $this->isVersionStringPrinted = true;
    }

    /**
     * Show the help message
     */
    protected function printHelp() : void
    {
        $this->printVersionString();
        echo <<<EOT
Usage: phpbu [option]

  --bootstrap=<file>       A "bootstrap" PHP file that is included before the backup.
  --configuration=<file>   A phpbu configuration file.
  --colors                 Use colors in output.
  --debug                  Display debugging information during backup generation.
  --generate-configuration Create a new configuration skeleton.
  --limit=<subset>         Limit backup execution to a subset.
  --simulate               Perform a trial run with no changes made.
  --restore                Print a restore guide.
  -h, --help               Print this usage information.
  -v, --verbose            Output more verbose information.
  -V, --version            Output version information and exit.

EOT;
        if ($this->isPhar) {
            echo '  --version-check        Check whether phpbu is up to date.' . PHP_EOL;
            echo '  --self-update          Upgrade phpbu to the latest version.' . PHP_EOL;
        }
    }

    /**
     * Shows some given error message
     *
     * @param string $message
     * @param bool   $hint
     */
    private function printError($message, $hint = false) : void
    {
        $help = $hint ? ', use "phpbu -h" for help' : '';
        $this->printVersionString();
        echo $message . $help . PHP_EOL;
        exit(self::EXIT_EXCEPTION);
    }

    /**
     * Main method, is called by phpbu command and the phar file
     */
    public static function main() : void
    {
        $app = new static();
        $app->run($_SERVER['argv']);
    }
}
