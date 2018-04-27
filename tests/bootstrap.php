<?php
define('PHPBU_TEST_FILES', realpath(__DIR__ . '/_files'));
define('PHPBU_TEST_BIN', realpath(__DIR__ . '/_files/bin'));
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/phpbu/Backup/Cleaner/TestCase.php';
require __DIR__ . '/phpbu/Backup/CliMockery.php';
require __DIR__ . '/phpbu/BaseMockery.php';
require __DIR__ . '/phpbu/FakeAdapter.php';
require __DIR__ . '/phpbu/Log/NullLogger.php';
require __DIR__ . '/phpbu/Runner/Mockery.php';
