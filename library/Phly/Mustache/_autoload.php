<?php
namespace Mustache;
$_map = array (
  'Phly\\Mustache\\Lexer' => __DIR__ . DIRECTORY_SEPARATOR . 'Lexer.php',
  'Phly\\Mustache\\Exception\\InvalidVariableNameException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidVariableNameException.php',
  'Phly\\Mustache\\Exception\\InvalidPragmaNameException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidPragmaNameException.php',
  'Phly\\Mustache\\Exception\\UnregisteredPragmaException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/UnregisteredPragmaException.php',
  'Phly\\Mustache\\Exception\\InvalidStateException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidStateException.php',
  'Phly\\Mustache\\Exception\\InvalidTemplatePathException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidTemplatePathException.php',
  'Phly\\Mustache\\Exception\\TemplateNotFoundException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/TemplateNotFoundException.php',
  'Phly\\Mustache\\Exception\\InvalidTemplateException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidTemplateException.php',
  'Phly\\Mustache\\Exception\\UnbalancedTagException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/UnbalancedTagException.php',
  'Phly\\Mustache\\Exception\\InvalidDelimiterException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidDelimiterException.php',
  'Phly\\Mustache\\Exception\\InvalidPartialsException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidPartialsException.php',
  'Phly\\Mustache\\Exception\\InvalidEscaperException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/InvalidEscaperException.php',
  'Phly\\Mustache\\Exception\\UnbalancedSectionException' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception/UnbalancedSectionException.php',
  'Phly\\Mustache\\Mustache' => __DIR__ . DIRECTORY_SEPARATOR . 'Mustache.php',
  'Phly\\Mustache\\Renderer' => __DIR__ . DIRECTORY_SEPARATOR . 'Renderer.php',
  'Phly\\Mustache\\Exception' => __DIR__ . DIRECTORY_SEPARATOR . 'Exception.php',
  'Phly\\Mustache\\Pragma\\AbstractPragma' => __DIR__ . DIRECTORY_SEPARATOR . 'Pragma/AbstractPragma.php',
  'Phly\\Mustache\\Pragma\\ImplicitIterator' => __DIR__ . DIRECTORY_SEPARATOR . 'Pragma/ImplicitIterator.php',
  'Phly\\Mustache\\Pragma\\SubView' => __DIR__ . DIRECTORY_SEPARATOR . 'Pragma/SubView.php',
  'Phly\\Mustache\\Pragma\\SubViews' => __DIR__ . DIRECTORY_SEPARATOR . 'Pragma/SubViews.php',
  'Phly\\Mustache\\Pragma' => __DIR__ . DIRECTORY_SEPARATOR . 'Pragma.php',
);
spl_autoload_register(function($class) use ($_map) {
    if (array_key_exists($class, $_map)) {
        require_once $_map[$class];
    }
});
