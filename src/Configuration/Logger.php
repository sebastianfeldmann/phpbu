<?php
namespace phpbu\App\Configuration;

/**
 * Logger configuration.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Logger extends Optionized
{
    /**
     * Logger type.
     *
     * @var string
     */
    public $type;

    /**
     * Constructor.
     *
     * @param string $type
     * @param array  $options
     */
    public function __construct($type, $options = array())
    {
        $this->type = $type;
        $this->setOptions($options);
    }
}
