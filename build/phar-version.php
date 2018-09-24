#!/usr/bin/env php
<?php
if (!isset($argv[1])) {
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

$version = trim($argv[1]);

if (strpos($version, 'dev') !== false) {
    // smuggle in a more informative dev version number
    try {
        $repository = new SebastianFeldmann\Git\Repository(dirname(__DIR__));
        $info       = $repository->getInfoOperator();
        $version    = $info->getCurrentTag();

        file_put_contents(
            __DIR__ . '/phar/Version.php',
            preg_replace(
                '#new self\\(\'.*\',#',
                'new self(\'' . $version . '\',',
                file_get_contents(__DIR__ . '/phar/Version.php')
            )
        );
    } catch (\Exception $e) {
        exit(1);
    }
}

echo $version;
