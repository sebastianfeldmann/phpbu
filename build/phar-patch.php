#!/usr/bin/env php
<?php

$patches = array(
    array(
        'path'    => __DIR__ . '/phar/dropbox/Curl.php',
        'search'  => array('$this->set(CURLOPT_CAINFO', '$this->set(CURLOPT_CAPATH'),
        'replace' => array('//$this->set(CURLOPT_CAINFO', '//$this->set(CURLOPT_CAPATH'),
    ),
);

foreach ( $patches as $file ) {
    echo 'patching file: ' . $file['path'] . "...";
    file_put_contents(
        $file['path'],
        str_replace(
            $file['search'],
            $file['replace'],
            file_get_contents($file['path'])
        )
    );
    echo ' done' . PHP_EOL;
};
