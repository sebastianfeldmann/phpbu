<?php
namespace phpbu\App\Backup;

use phpbu\App\Result;
use SebastianFeldmann\Cli\Command\Result as CommandResult;
use SebastianFeldmann\Cli\Command\Runner;
use SebastianFeldmann\Cli\Command\Runner\Result as RunnerResult;

/**
 * CliMockery trait
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
trait CliMockery
{
    /**
     * Create App\Result mock.
     *
     * @return \phpbu\App\Result
     */
    protected function getAppResultMock()
    {
        return $this->createMock(Result::class);
    }

    /**
     * Create CLI Runner mock.
     *
     * @return \SebastianFeldmann\Cli\Command\Runner
     */
    protected function getRunnerMock()
    {
        return $this->createMock(Runner::class);
    }

    /**
     * Create runner result mock.
     *
     * @param  int    $code
     * @param  string $cmd
     * @param  string $out
     * @param  string $err
     * @param  string $redirect
     * @param  int[]  $acceptableExitCodes
     * @return \SebastianFeldmann\Cli\Command\Runner\Result
     */
    protected function getRunnerResultMock(
        int $code,
        string $cmd,
        string $out = '',
        string $err = '',
        string $redirect = '',
        array $acceptableExitCodes = [0]
    ) {
        $cmdRes = new CommandResult($cmd, $code, $out, $err, $redirect, $acceptableExitCodes);
        $runRes = new RunnerResult($cmdRes);

        return $runRes;
    }

    /**
     * Create Cli\Result mock.
     *
     * @param  integer $code
     * @param  string  $cmd
     * @param  string  $output
     * @return \SebastianFeldmann\Cli\Command\Result
     */
    protected function getCliResultMock($code, $cmd, $output = '')
    {
        $cliResult = $this->createMock(CommandResult::class);

        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getCmd')->willReturn($cmd);
        $cliResult->method('getStdOut')->willReturn($output);
        $cliResult->method('getStdOutAsArray')->willReturn(explode(PHP_EOL, $output));
        $cliResult->method('isSuccessful')->willReturn($code == 0);

        return $cliResult;
    }
}
