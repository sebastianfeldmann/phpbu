<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Class FakeCrypter
 *
 * Dummy class to test the phpbu factory.
 */
class FakeCrypter implements Crypter
{
    /**
     * Do nothing.
     */
    public function doNothing()
    {
        // do something fooish
    }

    /**
     * Setup the Crypter.
     *
     * @param array $options
     */
    public function setup(array $options = [])
    {
        // do something fooish
    }

    /**
     * Checks the created backup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result
     * @throws \phpbu\App\Exception
     */
    public function crypt(Target $target, Result $result)
    {
        // do something fooish
    }

    /**
     * Return the encrypted file suffix.
     *
     * @return string
     */
    public function getSuffix()
    {
        return 'mc';
    }
}
