<?php
function Phly_Mustache_autoload($class) 
{
    static $map = null;
    if (null === $map) {
        $map = array (
            'Phly_Mustache_Lexer' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Lexer.php',
            'Phly_Mustache_Exception_InvalidVariableNameException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidVariableNameException.php',
            'Phly_Mustache_Exception_InvalidPragmaNameException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidPragmaNameException.php',
            'Phly_Mustache_Exception_UnregisteredPragmaException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/UnregisteredPragmaException.php',
            'Phly_Mustache_Exception_InvalidStateException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidStateException.php',
            'Phly_Mustache_Exception_InvalidTemplatePathException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidTemplatePathException.php',
            'Phly_Mustache_Exception_TemplateNotFoundException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/TemplateNotFoundException.php',
            'Phly_Mustache_Exception_InvalidTemplateException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidTemplateException.php',
            'Phly_Mustache_Exception_UnbalancedTagException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/UnbalancedTagException.php',
            'Phly_Mustache_Exception_InvalidDelimiterException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidDelimiterException.php',
            'Phly_Mustache_Exception_InvalidPartialsException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidPartialsException.php',
            'Phly_Mustache_Exception_InvalidEscaperException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/InvalidEscaperException.php',
            'Phly_Mustache_Exception_UnbalancedSectionException' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception/UnbalancedSectionException.php',
            'Phly_Mustache_Mustache' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Mustache.php',
            'Phly_Mustache_Renderer' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Renderer.php',
            'Phly_Mustache_Exception' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception.php',
            'Phly_Mustache_Pragma_AbstractPragma' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pragma/AbstractPragma.php',
            'Phly_Mustache_Pragma_ImplicitIterator' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pragma/ImplicitIterator.php',
            'Phly_Mustache_Pragma_SubView' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pragma/SubView.php',
            'Phly_Mustache_Pragma_SubViews' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pragma/SubViews.php',
            'Phly_Mustache_Pragma' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Pragma.php',
        );
    }
    if (array_key_exists($class, $map)) {
        require_once $map[$class];
    }
}
spl_autoload_register('Phly_Mustache_autoload');
