<?php
namespace phpbu\App;

use phpbu\App\Exception;

/**
 * Cli argument parser.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Args
{

    /**
     * List of available - options.
     *
     * @var array
     */
    private $shortOptions = array(
        'h' => true,
        'v' => true,
        'V' => true,
    );

    /**
     * List of available -- options.
     *
     * @var array
     */
    private $longOptions = array(
        'bootstrap='     => true,
        'colors'         => true,
        'configuration=' => true,
        'debug'          => true,
        'help'           => true,
        'include-path='  => true,
        'verbose'        => true,
        'version'        => true
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (defined('__PHPBU_PHAR__')) {
            $this->longOptions['selfupdate']  = true;
            $this->longOptions['self-update'] = true;
        }
    }

    /**
     * Get all cli options.
     *
     * @param  array $args
     * @return array
     */
    public function getOptions(array $args)
    {
        // remove scriptname from args
        if (isset($args[0][0]) && $args[0][0] != '-') {
            array_shift($args);
        }

        $options = array();

        reset($args);
        array_map('trim', $args);

        foreach ($args as $i => $arg) {
            $argLength = strlen($arg);
            // if empty arg or arg doesn't start with "-" skip it
            if (empty($arg) || $arg == '--' || $arg[0] != '-') {
                continue;
            }
            if ($argLength > 1 && $arg[1] == '-') {
                $this->parseLongOption(substr($arg, 2), $options);
            } else {
                $this->parseShortOption(substr($arg, 1), $options);
            }
        }
        return $options;
    }

    /**
     * Check short option and put into option list.
     *
     * @param  string $arg
     * @param  array $options
     * @throws phpbu\App\Exception
     */
    public function parseShortOption($arg, array &$options)
    {
        if (!isset($this->shortOptions[$arg])) {
            throw new Exception('unknown option: -' . $arg);
        }
        $options['-' . $arg] = true;
    }

    /**
     * Check long option and put into otion list.
     *
     * @param  string $arg
     * @param  array $options
     * @throws phpbu\App\Exception
     */
    public function parseLongOption($arg, array &$options)
    {
        $list     = explode('=', $arg);
        $option   = $list[0];
        $argument = true;

        if (count($list) > 1) {
            $argument = $list[1];
        }
        if (count($list) > 2) {
            throw new Exception('invalid value for option: --' . $arg);
        }
        if (!isset($this->longOptions[$option]) && !isset($this->longOptions[$option . '='])) {
            throw new Exception('unknown option: --' . $option);
        }
        if ($argument === true && isset($this->longOptions[$option . '='])) {
            throw new Exception('argument required for option: --' . $option);
        }
        if ($argument !== true && isset($this->longOptions[$option])) {
            throw new Exception('needless argument for option: --' . $option);
        }
        $options['--' . $option] = $argument;
    }
}
