<?php
namespace phpbu\App\Configuration;

/**
 * Optionized Configuration
 *
 * @package    phpbu
 * @subpackage Configuration
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
abstract class Optionized
{
    /**
     * Configuration Options
     *
     * @var array
     */
    public $options;

    /**
     * Options setter.
     *
     * @param array $options
     */
    protected function setOptions(array $options)
    {
        $this->options = $options;
    }
}
