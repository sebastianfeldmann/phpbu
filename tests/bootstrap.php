<?php
// define test path constants for test files and test binaries
define('PHPBU_TEST_FILES', realpath(__DIR__ . '/_files'));
define('PHPBU_TEST_BIN', realpath(__DIR__ . '/_files/bin'));

// load mocking traits and base and fake classes
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/phpbu/Backup/Cleaner/TestCase.php';
require __DIR__ . '/phpbu/Backup/CliMockery.php';
require __DIR__ . '/phpbu/BaseMockery.php';
require __DIR__ . '/phpbu/Factory/FakeCheck.php';
require __DIR__ . '/phpbu/Factory/FakeCrypter.php';
require __DIR__ . '/phpbu/Factory/FakeLoggerNoListener.php';
require __DIR__ . '/phpbu/Factory/FakeNothing.php';
require __DIR__ . '/phpbu/Factory/FakeRunner.php';
require __DIR__ . '/phpbu/Factory/FakeSource.php';
require __DIR__ . '/phpbu/FakeAdapter.php';
require __DIR__ . '/phpbu/Log/NullLogger.php';
require __DIR__ . '/phpbu/Runner/Mockery.php';
