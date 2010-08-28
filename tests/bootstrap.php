<?php
// library autoloader
include __DIR__ . '/../library/Phly/Mustache/_autoload.php';

// test autoloader
spl_autoload_register(function($class) {
    if ('PhlyTest' !== substr($class, 0, 8)) {
        return;
    }
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    include_once $file;
});
