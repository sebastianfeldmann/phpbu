<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Configuration\Optionized;

/**
 * Crypt Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Crypt extends Optionized
{
    /**
     * Crypt type
     *
     * @var string
     */
    public $type;

    /**
     * Skip crypt
     *
     * @var boolean
     */
    public $skipOnFailure;

    /**
     * Constructor.
     *
     * @param string  $type
     * @param boolean $skipOnFailure
     * @param array   $options
     */
    public function __construct($type, $skipOnFailure, $options = array())
    {
        $this->type          = $type;
        $this->skipOnFailure = $skipOnFailure;
        $this->setOptions($options);
    }
}
