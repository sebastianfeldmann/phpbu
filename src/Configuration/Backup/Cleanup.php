<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Configuration\Optionized;

/**
 * Cleanup Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Cleanup extends Optionized
{
    /**
     * Cleanup type
     *
     * @var string
     */
    public $type;

    /**
     * Skip cleanup on previous failure.
     *
     * @var boolean
     */
    public $skipOnFailure;

    /**
     * Constructor
     *
     * @param string  $type
     * @param boolean $skipOnFailure
     * @param array   $options
     */
    public function __construct($type, $skipOnFailure, $options = [])
    {
        $this->type          = $type;
        $this->skipOnFailure = $skipOnFailure;
        $this->setOptions($options);
    }
}
