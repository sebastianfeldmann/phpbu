<?php
namespace phpbu\App\Configuration\Backup;

use phpbu\App\Configuration\Optionized;

/**
 * Source Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Source extends Optionized
{
    /**
     * Source type
     *
     * @var string
     */
    public $type;

    /**
     * Constructor
     *
     * @param string $type
     * @param array  $options
     */
    public function __construct($type, $options = [])
    {
        $this->type = $type;
        $this->setOptions($options);
    }
}
