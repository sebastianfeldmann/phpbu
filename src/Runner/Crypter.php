<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Crypter as CrypterExe;
use phpbu\App\Backup\Crypter\Simulator;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;

/**
 * Crypter Runner
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Crypter extends Abstraction
{
    /**
     * Execute or simulate the encryption.
     *
     * @param  \phpbu\App\Backup\Crypter $crypter
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    public function run(CrypterExe $crypter, Target $target, Result $result)
    {
        if ($this->isSimulation()) {
            $this->simulate($crypter, $target, $result);
        } else {
            $this->crypt($crypter, $target, $result);
        }
        $target->setCrypter($crypter);
    }

    /**
     * Execute the encryption.
     *
     * @param  \phpbu\App\Backup\Crypter $crypter
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    protected function crypt(CrypterExe $crypter, Target $target, Result $result)
    {
        $crypter->crypt($target, $result);
    }

    /**
     * Simulate the encryption.
     *
     * @param  \phpbu\App\Backup\Crypter $crypter
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    protected function simulate(CrypterExe $crypter, Target $target, Result $result)
    {
        if ($crypter instanceof Simulator) {
            $crypter->simulate($target, $result);
        }
    }
}
