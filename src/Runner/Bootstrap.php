<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Exception;

/**
 * Bootstrap Runner
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Bootstrap extends Abstraction
{
    /**
     * Execute bootstrap runner.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    public function run(Configuration $configuration)
    {
        $this->handleBootstrap($configuration);
    }

    /**
     * Handles the bootstrap file inclusion.
     *
     * @param  \phpbu\App\Configuration $configuration
     * @throws \phpbu\App\Exception
     */
    protected function handleBootstrap(Configuration $configuration)
    {
        $filename = $configuration->getBootstrap();

        if (!empty($filename)) {
            $pathToFile = stream_resolve_include_path($filename);
            if (!$pathToFile || !is_readable($pathToFile)) {
                throw new Exception(sprintf('Cannot open bootstrap file "%s".' . PHP_EOL, $filename));
            }
            require $pathToFile;
        }
    }
}
