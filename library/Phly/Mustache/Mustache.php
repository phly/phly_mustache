<?php

namespace Phly\Mustache;

use ArrayObject,
    SplStack;

/**
 * Mustache implementation
 * 
 * @todo Allow specifying an alternate template suffix
 * @todo Prevent duplicate paths from being added
 * @license New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Mustache
{
    /** @var array Cached file-based templates; contains template name/token pairs */
    protected $cachedTemplates = array();

    /** @var SplStack Stack of template paths to search */
    protected $templatePath;

    /** @var Lexer */
    protected $lexer;

    /** @var Renderer */
    protected $renderer;

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->templatePath = new SplStack;
    }

    /**
     * Set lexer to use when tokenizing templates
     * 
     * @param  Lexer $lexer 
     * @return Mustache
     */
    public function setLexer(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->lexer->setManager($this);
        return $this;
    }

    /**
     * Get lexer
     * 
     * @return Lexer
     */
    public function getLexer()
    {
        if (null === $this->lexer) {
            $this->setLexer(new Lexer());
        }
        return $this->lexer;
    }

    /**
     * Set renderer
     * 
     * @param  Renderer $renderer 
     * @return Mustache
     */
    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->renderer->setManager($this);
        return $this;
    }

    /**
     * Get renderer
     * 
     * @return Renderer
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->setRenderer(new Renderer());
        }
        return $this->renderer;
    }

    /**
     * Add a template path to the template path stack
     * 
     * @param  string $path 
     * @return Mustache
     * @throws InvalidTemplatePathException
     */
    public function setTemplatePath($path)
    {
        if (!is_dir($path)) {
            throw new InvalidTemplatePathException();
        }
        $this->templatePath->push($path);
        return $this;
    }

    /**
     * Render a template using a view, and optionally a list of partials
     * 
     * @todo   should partials be passed here? or simply referenced?
     * @param  string $template Either a template string or a template file in the template path
     * @param  array|object $view An array or object with items to inject in the template
     * @param  array|object $partials A list of partial names/template pairs for rendering as partials
     * @return string
     * @throws InvalidPartialsException
     */
    public function render($template, $view, $partials = null)
    {
        // Tokenize and alias partials
        $tokenizedPartials = array();
        if (null !== $partials) {
            if (!is_array($partials) && !is_object($partials)) {
                throw new InvalidPartialsException();
            }

            // Get tokenized partials
            foreach ($partials as $alias => $partialTemplate) {
                if (!is_string($partialTemplate)) {
                    continue;
                }
                $tokenizedPartials[$alias] = $this->tokenize($partialTemplate);

                // Cache under this alias as well
                $this->cachedTemplates[$alias] = $tokenizedPartials[$alias];
            }
        }

        $tokens = $this->tokenize($template);

        $renderer = $this->getRenderer();
        return $renderer->render($tokens, $view, $tokenizedPartials);
    }

    /**
     * Tokenize a template
     * 
     * @param  string $template Either a template string or a reference to a template
     * @return array Array of tokens
     */
    public function tokenize($template)
    {
        $lexer = $this->getLexer();
        if (false !== strstr($template, '{{')) {
            return $lexer->compile($template);
        }

        if (array_key_exists($template, $this->cachedTemplates)) {
            return $this->cachedTemplates[$template];
        }

        $templateString = $this->fetchTemplate($template);
        $tokens = $lexer->compile($templateString);
        $this->cachedTemplates[$template] = $tokens;
        return $tokens;
    }

    /**
     * Locate and retrieve a template in the template path stack
     * 
     * @param  string $template 
     * @return string
     * @throws TemplateNotFoundException
     */
    protected function fetchTemplate($template)
    {
        foreach ($this->templatePath as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $template . '.html';
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->cachedTemplates[$template] = $content;
                return $content;
            }
        }
        throw new TemplateNotFoundException('Template by name "' . $template . '" not found');
    }
}
