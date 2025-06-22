<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Exception;
use phpbu\App\Factory;

/**
 * Runner Mockery
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
trait Mockery
{
    /**
     * Create Factory stub.
     *
     * @param  \phpbu\App\Backup\Source $source
     * @param  \phpbu\App\Backup\Target $target
     * @param  bool                     $passChecks
     * @param  int                      $backups
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMock($source, $passChecks, $backups = 1, $target = null)
    {
        $target   = $target ?? $this->createTargetMock('/tmp/foo', '/tmp/foo.gz');
        $runCalls = $passChecks ? 1 : 0;
        $check    = $this->createCheckMock($passChecks);
        $crypt    = $this->createCryptMock();
        $sync     = $this->createSyncMock();
        $cleanup  = $this->createCleanerMock();
        $factory  = $this->createMock(Factory::class);

        $factory->expects($this->exactly($backups))->method('createTarget')->willReturn($target);
        $factory->expects($this->exactly($backups))->method('createSource')->willReturn($source);
        $factory->expects($this->exactly($backups))->method('createCheck')->willReturn($check);
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
        $source  = $this->createSourceMock($this->createStatusMock());
        $factory = $this->createMock(Factory::class);
        $check   = $this->createCheckMock();
        $check->method('pass')->will($this->throwException(new Exception));

        $factory->expects($this->once())
                ->method('createTarget')
                ->willReturn($this->createTargetMock('/tmp/foo', '/tmp/foo.gz'));
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
        $source  = $this->createSourceMock($this->createStatusMock());
        $check   = $this->createCheckMock();
        $crypt   = $this->createCryptMock(false);
        $factory = $this->createMock(Factory::class);

        $factory->expects($this->once())
                ->method('createTarget')
                ->willReturn($this->createTargetMock('/tmp/foo', '/tmp/foo.gz'));
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->once())->method('createCrypter')->willReturn($crypt);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockSyncCrash()
    {
        $source  = $this->createSourceMock($this->createStatusMock());
        $check   = $this->createCheckMock();
        $crypt   = $this->createCryptMock();
        $sync    = $this->createSyncMock(false);
        $factory = $this->createMock(Factory::class);

        $factory->expects($this->once())
                ->method('createTarget')
                ->willReturn($this->createTargetMock('/tmp/foo', '/tmp/foo.gz'));
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->once())->method('createCrypter')->willReturn($crypt);
        $factory->expects($this->once())->method('createSync')->willReturn($sync);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockCleanerCrash()
    {
        $source  = $this->createSourceMock($this->createStatusMock());
        $check   = $this->createCheckMock();
        $crypt   = $this->createCryptMock();
        $sync    = $this->createSyncMock();
        $cleanup = $this->createCleanerMock(false);
        $factory = $this->createMock(Factory::class);

        $factory->method('createTarget')
                ->willReturn($this->createTargetMock('/tmp/foo', '/tmp/foo.gz'));
        $factory->expects($this->once())->method('createSource')->willReturn($source);
        $factory->expects($this->once())->method('createCheck')->willReturn($check);
        $factory->expects($this->once())->method('createCrypter')->willReturn($crypt);
        $factory->expects($this->once())->method('createSync')->willReturn($sync);
        $factory->expects($this->once())->method('createCleaner')->willReturn($cleanup);

        return $factory;
    }

    /**
     * Create Factory stub.
     *
     * @return \phpbu\App\Factory
     */
    protected function createFactoryMockStopOnFailure()
    {
        $factory = $this->createMock(Factory::class);
        $source  = $this->createSourceMock($this->createStatusMock());
        $source->expects($this->once())
               ->method('backup')
               ->will($this->throwException(new Exception()));

        $factory->expects($this->once())
                ->method('createTarget')
                ->willReturn($this->createTargetMock('/tmp/foo', '/tmp/foo.gz'));
        $factory->expects($this->once())->method('createSource')->willReturn($source);

        return $factory;
    }

    /**
     * Create Configuration Stub
     *
     * @param  int $amountOfBackups
     * @return \phpbu\App\Configuration
     */
    protected function createConfigurationMock($amountOfBackups = 1)
    {
        $check         = new Configuration\Backup\Check('SizeMin', '10m');
        $crypt         = new Configuration\Backup\Crypt('openssl', true, []);
        $sync          = new Configuration\Backup\Sync('rsync', true, []);
        $clean         = new Configuration\Backup\Cleanup('outdated', true, []);
        $configuration = $this->createMock(Configuration::class);

        $backups = [];
        for ($i = 0; $i < $amountOfBackups; $i++) {
            $backup = new Configuration\Backup('test' . $i, true);
            $backup->setTarget(new Configuration\Backup\Target('/tmp', 'foo.tar', 'gzip'));
            $backup->setSource(new Configuration\Backup\Source('tar', []));
            $backup->addCheck($check);
            $backup->setCrypt($crypt);
            $backup->addSync($sync);
            $backup->setCleanup($clean);

            $backups[] = $backup;
        }

        $configuration->method('getBackups')->willReturn($backups);
        $configuration->method('getWorkingDirectory')->willReturn('/tmp');
        $configuration->method('isSimulation')->willReturn(false);
        $configuration->method('isRestore')->willReturn(false);

        return $configuration;
    }
}
