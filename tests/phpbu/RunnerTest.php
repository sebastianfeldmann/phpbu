<?php
namespace phpbu\App;

/**
 * Runner Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.1.0
 */
class RunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Runner::run
     */
    public function testRunAllGood()
    {
        $factory       = $this->createFactoryMock(true);
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunCheckFail()
    {
        $factory       = $this->createFactoryMock(false);
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunCheckCrash()
    {
        $factory       = $this->createFactoryMockCheckCrash();
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunCryptCrash()
    {
        $factory       = $this->createFactoryMockCryptCrash();
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunSyncCrash()
    {
        $factory       = $this->createFactoryMockSyncCrash();
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunCleanerCrash()
    {
        $factory       = $this->createFactoryMockCleanerCrash();
        $configuration = $this->createConfigurationMock();

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Tests Runner::run
     */
    public function testRunStopOnFailure()
    {
        $factory       = $this->createFactoryMockStopOnFailure();
        $configuration = $this->createConfigurationMock(2);

        $runner = new Runner($factory);
        $runner->run($configuration);
    }

    /**
     * Create Factory stub.
     *
     * @param  bool $passChecks
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMock($passChecks)
    {
        $runCalls        = $passChecks ? 1 : 0;
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock();
        $source          = $this->createSourceMock();
        $checkRunner     = $this->createCheckRunnerMock($passChecks);
        $check           = $this->createCheckMock();
        $cryptRunner     = $this->createCryptRunnerMock($runCalls);
        $crypt           = $this->createCryptMock();
        $syncRunner      = $this->createSyncRunnerMock($runCalls);
        $sync            = $this->createSyncMock();
        $cleanupRunner   = $this->createCleanerRunnerMock($runCalls);
        $cleanup         = $this->createCleanerMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner,
                    $checkRunner,
                    $cryptRunner,
                    $syncRunner,
                    $cleanupRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->exactly($runCalls))->method('createCrypter')->willReturn($crypt);
        $factory->expects($this->exactly($runCalls))->method('createSync')->willReturn($sync);
        $factory->expects($this->exactly($runCalls))->method('createCleaner')->willReturn($cleanup);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockCheckCrash()
    {
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock();
        $source          = $this->createSourceMock();
        $checkRunner     = $this->createCheckRunnerMock(false, true);
        $check           = $this->createCheckMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner,
                    $checkRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockCryptCrash()
    {
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock();
        $source          = $this->createSourceMock();
        $checkRunner     = $this->createCheckRunnerMock(true);
        $check           = $this->createCheckMock();
        $cryptRunner     = $this->createCryptRunnerMock(1, true);
        $crypt           = $this->createCryptMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner,
                    $checkRunner,
                    $cryptRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->exactly(1))->method('createCrypter')->willReturn($crypt);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockSyncCrash()
    {
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock();
        $source          = $this->createSourceMock();
        $checkRunner     = $this->createCheckRunnerMock(true);
        $check           = $this->createCheckMock();
        $cryptRunner     = $this->createCryptRunnerMock(1);
        $crypt           = $this->createCryptMock();
        $syncRunner      = $this->createSyncRunnerMock(1, true);
        $sync            = $this->createSyncMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner,
                    $checkRunner,
                    $cryptRunner,
                    $syncRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->exactly(1))->method('createCrypter')->willReturn($crypt);
        $factory->expects($this->exactly(1))->method('createSync')->willReturn($sync);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockCleanerCrash()
    {
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock();
        $source          = $this->createSourceMock();
        $checkRunner     = $this->createCheckRunnerMock(true);
        $check           = $this->createCheckMock();
        $cryptRunner     = $this->createCryptRunnerMock(1);
        $crypt           = $this->createCryptMock();
        $syncRunner      = $this->createSyncRunnerMock(1);
        $sync            = $this->createSyncMock();
        $cleanupRunner   = $this->createCleanerRunnerMock(1, true);
        $cleanup         = $this->createCleanerMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner,
                    $checkRunner,
                    $cryptRunner,
                    $syncRunner,
                    $cleanupRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->exactly(1))->method('createCrypter')->willReturn($crypt);
        $factory->expects($this->exactly(1))->method('createSync')->willReturn($sync);
        $factory->expects($this->exactly(1))->method('createCleaner')->willReturn($cleanup);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockStopOnFailure()
    {
        $bootstrapRunner = $this->createBootstrapRunnerMock();
        $logger          = $this->createLoggerMock();
        $sourceRunner    = $this->createSourceRunnerMock(true);
        $source          = $this->createSourceMock();

        $factory = $this->getMockBuilder('\\phpbu\\App\\Factory')
                        ->disableOriginalConstructor()
                        ->getMock();

        $factory->method('createRunner')
                ->will($this->onConsecutiveCalls(
                    $bootstrapRunner,
                    $sourceRunner
                ));

        $factory->expects($this->once())->method('createLogger')->willReturn($logger);
        $factory->expects($this->once())->method('createSource')->willReturn($source);

        return $factory;
    }

    /**
     * Create bootstrap runner mock.
     *
     * @return \phpbu\App\Runner\Bootstrap
     */
    protected function createBootstrapRunnerMock()
    {
        $bootstrapRunner = $this->getMockBuilder('\\phpbu\\App\\Runner\\Bootstrap')
                                ->disableOriginalConstructor()
                                ->getMock();
        $bootstrapRunner->expects($this->once())->method('run');

        return $bootstrapRunner;
    }

    /**
     * Create logger mock.
     *
     * @return \phpbu\App\Log\Json
     */
    protected function createLoggerMock()
    {
        return new LoggerNull();
    }

    /**
     * Create source runner.
     *
     * @param  bool $crash
     * @return \phpbu\App\Runner\Source
     */
    protected function createSourceRunnerMock($crash = false)
    {
        $sourceRunner  = $this->getMockBuilder('\\phpbu\\App\\Runner\\Source')
                              ->disableOriginalConstructor()
                              ->getMock();
        if ($crash) {
            $sourceRunner->expects($this->once())->method('run')->will($this->throwException(new Exception('fail')));
        } else {
            $sourceRunner->expects($this->once())->method('run');
        }

        return $sourceRunner;
    }

    /**
     * Create source mock.
     *
     * @return \phpbu\App\Backup\Source\Tar
     */
    protected function createSourceMock()
    {
        return $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Tar')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create check runner mock.
     *
     * @param  bool $pass
     * @param  bool $crash
     * @return \phpbu\App\Runner\Check
     */
    protected function createCheckRunnerMock($pass, $crash = false)
    {
        $checkRunner = $this->getMockBuilder('\\phpbu\\App\\Runner\\Check')
                            ->disableOriginalConstructor()
                            ->getMock();
        if ($crash) {
            $checkRunner->expects($this->once())->method('run')->will($this->throwException(new Exception('fail')));
        } else {
            $checkRunner->expects($this->once())->method('run')->willReturn($pass);
        }

        return $checkRunner;
    }

    /**
     * Create check mock.
     *
     * @return \phpbu\App\Backup\Check\SizeMin
     */
    protected function createCheckMock()
    {
        return $this->getMockBuilder('\\phpbu\\App\\Backup\\Check\\SizeMin')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create crypt runner mock.
     *
     * @param  int  $runCalls
     * @param  bool $crash
     * @return \phpbu\App\Runner\Crypter
     */
    protected function createCryptRunnerMock($runCalls, $crash = false)
    {
        $cryptRunner = $this->getMockBuilder('\\phpbu\\App\\Runner\\Crypter')
                            ->disableOriginalConstructor()
                            ->getMock();

        if ($crash) {
            $cryptRunner->expects($this->once())
                        ->method('run')
                        ->will($this->throwException(new Backup\Crypter\Exception('fail')));
        } else {
            $cryptRunner->expects($this->exactly($runCalls))->method('run');
        }

        return $cryptRunner;
    }

    /**
     * Create crypt mock
     *
     * @return \phpbu\App\Backup\Crypter\OpenSSL
     */
    protected function createCryptMock()
    {
        return $this->getMockBuilder('\\phpbu\\App\\Backup\\Crypter\\OpenSSL')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create sync runner mock
     *
     * @param  int  $runCalls
     * @param  bool $crash
     * @return \phpbu\App\Runner\Sync
     */
    protected function createSyncRunnerMock($runCalls, $crash = false)
    {
        $syncRunner = $this->getMockBuilder('\\phpbu\\App\\Runner\\Sync')
                           ->disableOriginalConstructor()
                           ->getMock();

        if ($crash) {
            $syncRunner->expects($this->once())
                       ->method('run')
                       ->will($this->throwException(new Backup\Sync\Exception('fail')));
        } else {
            $syncRunner->expects($this->exactly($runCalls))->method('run');
        }

        return $syncRunner;
    }

    /**
     * Create sync mock
     *
     * @return \phpbu\App\Backup\Sync\Rsync
     */
    protected function createSyncMock()
    {
        return $this->getMockBuilder('\\phpbu\\App\\Backup\\Sync\\Rsync')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create cleanup runner mock
     *
     * @param  int  $runCalls
     * @param  bool $crash
     * @return \phpbu\App\Runner\Cleanup
     */
    protected function createCleanerRunnerMock($runCalls, $crash = false)
    {
        $cleanupRunner = $this->getMockBuilder('\\phpbu\\App\\Runner\\Cleaner')
                              ->disableOriginalConstructor()
                              ->getMock();

        if ($crash) {
            $cleanupRunner->expects($this->once())
                          ->method('run')
                          ->will($this->throwException(new Backup\Cleaner\Exception('fail')));
        } else {
            $cleanupRunner->expects($this->exactly($runCalls))->method('run');
        }

        return $cleanupRunner;
    }

    /**
     * Create cleanup mock.
     *
     * @return \phpbu\App\Backup\Cleaner\Outdated
     */
    protected function createCleanerMock()
    {
        return $cleanup = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cleaner\\Outdated')
                               ->disableOriginalConstructor()
                               ->getMock();
    }

    /**
     * Create Configuration Stub
     *
     * @param  int $amountOfBackups
     * @return \phpbu\App\Configuration
     */
    protected function createConfigurationMock($amountOfBackups = 1)
    {
        $check = new Configuration\Backup\Check('SizeMin', '10m');
        $crypt = new COnfiguration\Backup\Crypt('openssl', true, []);
        $sync  = new COnfiguration\Backup\Sync('rsync', true, []);
        $clean = new Configuration\Backup\Cleanup('outdated', true, []);
        $log   = new Configuration\Logger('json', []);

        $backup = new Configuration\Backup('test', true);
        $backup->setTarget(new Configuration\Backup\Target('/tmp', 'foo.tar', 'bzip2'));
        $backup->setSource(new Configuration\Backup\Source('tar', []));
        $backup->addCheck($check);
        $backup->setCrypt($crypt);
        $backup->addSync($sync);
        $backup->setCleanup($clean);

        $configuration = $this->getMockBuilder('\\phpbu\\App\\Configuration')
                              ->disableOriginalConstructor()
                              ->getMock();
        $configuration->method('getLoggers')->willReturn([$log, $this->createLoggerMock()]);

        $backups = [];
        for ($i = 0; $i < $amountOfBackups; $i++) {
            $backups[] = $backup;
        }

        $configuration->method('getBackups')->willReturn($backups);
        $configuration->method('getWorkingDirectory')->willReturn('/tmp');
        $configuration->method('isSimulation')->willReturn(false);

        return $configuration;
    }
}

class LoggerNull implements Listener
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     */
    public static function getSubscribedEvents()
    {
        return [];
    }
}
