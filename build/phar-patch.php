#!/usr/bin/env php
<?php

$patches = array();

$replacements = array();

foreach ($patches as $file) {
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

foreach ($replacements as $file) {
    echo 'replacing file: ' . $file['path'] . "...";
    file_put_contents($file['path'], $file['content']);
    echo ' done' . PHP_EOL;
}
