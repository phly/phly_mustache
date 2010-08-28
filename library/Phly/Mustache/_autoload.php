<?php
namespace Phly\Mustache;
$_map = array (
  'Phly\\Mustache\\Lexer' => __DIR__ . DIRECTORY_SEPARATOR . 'Lexer.php',
  'Phly\\Mustache\\InvalidTemplatePathException' => __DIR__ . DIRECTORY_SEPARATOR . 'InvalidTemplatePathException.php',
  'Phly\\Mustache\\Mustache' => __DIR__ . DIRECTORY_SEPARATOR . 'Mustache.php',
  'Phly\\Mustache\\TemplateNotFoundException' => __DIR__ . DIRECTORY_SEPARATOR . 'TemplateNotFoundException.php',
  'Phly\\Mustache\\InvalidPartialsException' => __DIR__ . DIRECTORY_SEPARATOR . 'InvalidPartialsException.php',
  'Phly\\Mustache\\Renderer' => __DIR__ . DIRECTORY_SEPARATOR . 'Renderer.php',
  'Phly\\Mustache\\Exception' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception.php',
);
spl_autoload_register(function($class) use ($_map) {
    if (array_key_exists($class, $_map)) {
        require_once $_map[$class];
    }
});
