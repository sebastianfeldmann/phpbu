<?php
namespace phpbu\App\Configuration;

/**
 * Adapter Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 4.0.0
 */
class Adapter extends Optionized
{
    /**
     * Adapter type
     *
     * @var string
     */
    public $type;

    /**
     * Adapter name
     *
     * @var string
     */
    public $name;

    /**
     * Adapter options
     *
     * @var array
     */
    public $options;

    /**
     * Constructor
     *
     * @param string $type
     * @param string $name
     * @param array  $options
     */
    public function __construct($type, $name, array $options = [])
    {
        $this->type    = $type;
        $this->name    = $name;
        $this->setOptions($options);
    }
}
