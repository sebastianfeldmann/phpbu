<?php
namespace phpbu\App\Configuration\Backup;

/**
 * Check Configuration
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 2.0.0
 */
class Check
{
    /**
     * Check type
     *
     * @var string
     */
    public $type;

    /**
     * Check value
     *
     * @var string
     */
    public $value;

    /**
     * Constructor
     *
     * @param string $type
     * @param string $value
     */
    public function __construct($type, $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }
}
