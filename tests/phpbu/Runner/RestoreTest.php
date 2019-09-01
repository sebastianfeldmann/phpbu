<?php

namespace phpbu\App\Runner;

use phpbu\App\Backup\Crypter\Mcrypt;
use phpbu\App\Backup\Crypter\OpenSSL;
use phpbu\App\Backup\Source\Redis;
use phpbu\App\Backup\Source\Status;
use phpbu\App\Backup\Source\Tar;
use phpbu\App\BaseMockery;
use phpbu\App\Configuration;
use phpbu\App\Factory;
use phpbu\App\Result;
use PHPUnit\Framework\TestCase;

class RestoreTest extends TestCase
{
    use BaseMockery;

    /**
     * Tests Restore::run
     */
    public function testRunBackupAndCryptUnsupported()
    {
        $source = $this->createMock(Redis::class);
        $crypt  = $this->createMock(Mcrypt::class);

        $backupConfig = new Configuration\Backup('test', true);
        $backupConfig->setTarget(new Configuration\Backup\Target('/tmp', 'foo.tar', 'gzip'));
        $backupConfig->setSource(new Configuration\Backup\Source('tar', []));
        $backupConfig->setCrypt(new Configuration\Backup\Crypt('mcrypt', true, []));

        $target        = $this->createTargetMock();
        $factory       = $this->createRestoreFactoryMock($target, $source, $crypt);
        $configuration = $this->createRestoreConfigurationMock([$backupConfig]);

        $result = new Result();
        $runner = new Restore($factory, $result);

        ob_start();
        $runner->run($configuration);
        $output = ob_get_clean();
    }

    /**
     * Tests Restore::run
     */
    public function testRunBackupsSupportedNoCrypt()
    {
        $status = $this->createMock(Status::class);
        $status->expects($this->once())->method('isDirectory')->willReturn(false);

        $source = $this->createMock(Tar::class);
        $source->expects($this->once())->method('restore')->willReturn($status);

        $backupConfig = new Configuration\Backup('test', true);
        $backupConfig->setTarget(new Configuration\Backup\Target('/tmp', 'foo.tar', 'gzip'));
        $backupConfig->setSource(new Configuration\Backup\Source('tar', []));

        $target        = $this->createTargetMock();
        $factory       = $this->createRestoreFactoryMock($target, $source);
        $configuration = $this->createRestoreConfigurationMock([$backupConfig]);

        $result = new Result();
        $runner = new Restore($factory, $result);

        ob_start();
        $runner->run($configuration);
        $output = ob_get_clean();
    }

    /**
     * Tests Restore::run
     */
    public function testRunBackupAndCryptSupported()
    {
        $source = new Tar();
        $source->setup(['path' => '/tmp']);

        $crypt  = new OpenSSL();
        $crypt->setup(['algorithm' => 'aes-256-cbc', 'password' => 'foo']);

        $backupConfig = new Configuration\Backup('test', true);
        $backupConfig->setTarget(new Configuration\Backup\Target('/tmp', 'foo.tar', 'gzip'));
        $backupConfig->setSource(new Configuration\Backup\Source('tar', []));
        $backupConfig->setCrypt(new Configuration\Backup\Crypt('mcrypt', true, []));

        $target        = $this->createTargetMock();
        $factory       = $this->createRestoreFactoryMock($target, $source, $crypt);
        $configuration = $this->createRestoreConfigurationMock([$backupConfig]);

        $result = new Result();
        $runner = new Restore($factory, $result);

        ob_start();
        $runner->run($configuration);
        $output = ob_get_clean();
    }

    /**
     * Create Configuration Stub
     *
     * @param  array $backups
     * @return \phpbu\App\Configuration
     */
    private function createRestoreConfigurationMock(array $backups)
    {
        $configuration = $this->createMock(Configuration::class);

        $configuration->expects($this->once())->method('getBackups')->willReturn($backups);
        $configuration->method('getWorkingDirectory')->willReturn('/tmp');
        $configuration->method('isSimulation')->willReturn(false);
        $configuration->method('isRestore')->willReturn(true);
        $configuration->method('isBackupActive')->willReturn(true);

        return $configuration;
    }

    /**
     * Create a restore factory mock
     *
     * @param  $target
     * @param  $source
     * @return \phpbu\App\Factory
     */
    private function createRestoreFactoryMock($target, $source, $crypt = null)
    {
        $factory  = $this->createMock(Factory::class);

        $factory->expects($this->exactly(1))->method('createTarget')->willReturn($target);
        $factory->expects($this->exactly(1))->method('createSource')->willReturn($source);
        if ($crypt) {
            $factory->expects($this->exactly(1))->method('createCrypter')->willReturn($crypt);
        }

        return $factory;
    }
}
