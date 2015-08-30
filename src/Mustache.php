<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache;

use ArrayObject;
use Traversable;

/**
 * Mustache implementation
 *
 * @todo Prevent duplicate paths from being added
 */
class Mustache
{
    /**
     * Cached file-based templates; contains template name/token pairs
     * @var array
     */
    protected $cachedTemplates = [];

    /**
     * Lexer
     * @var Lexer
     */
    protected $lexer;

    /**
     * Renderer
     * @var Renderer
     */
    protected $renderer;

    /**
     * Template resolver
     * @var Resolver\ResolverInterface
     */
    protected $resolver;

    /**
     * Suffix used when resolving templates
     * @var string
     */
    protected $suffix = '.mustache';

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
     * Set template resolver
     *
     * @param  Resolver\ResolverInterface $resolver
     * @return Mustache
     */
    public function setResolver(Resolver\ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * Get template resolver
     *
     * @return Resolver\ResolverInterface
     */
    public function getResolver()
    {
        if (!$this->resolver instanceof Resolver\ResolverInterface) {
            $resolver = new Resolver\AggregateResolver();
            $resolver->attach(new Resolver\DefaultResolver(), 0);
            $this->setResolver($resolver);
        }
        return $this->resolver;
    }

    /**
     * Render a template using a view, and optionally a list of partials
     *
     * @todo   should partials be passed here? or simply referenced?
     * @param  string $template Either a template string or a template file in the template path
     * @param  array|object $view An array or object with items to inject in the template
     * @param  array|object $partials A list of partial names/template pairs for rendering as partials
     * @return string
     * @throws Exception\InvalidPartialsException
     */
    public function render($template, $view, $partials = null)
    {
        // Tokenize and alias partials
        $tokenizedPartials = [];
        if (null !== $partials) {
            if (!is_array($partials) && !is_object($partials)) {
                throw new Exception\InvalidPartialsException();
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
     * @param  boolean $cacheTokens Whether or not to cache tokens for the given template
     * @return array Array of tokens
     */
    public function tokenize($template, $cacheTokens = true)
    {
        $lexer = $this->getLexer();
        if (false !== strstr($template, '{{')) {
            return $lexer->compile($template);
        }

        if ($cacheTokens
            && array_key_exists($template, $this->cachedTemplates)
            && is_array($this->cachedTemplates[$template])
        ) {
            return $this->cachedTemplates[$template];
        }

        $templateOrTokens = $this->fetchTemplate($template);

        if (is_string($templateOrTokens)) {
            $templateOrTokens = $lexer->compile($templateOrTokens, $template);
        }

        if ($templateOrTokens instanceof Traversable) {
            $templateOrTokens = iterator_to_array($templateOrTokens);
        }

        if (!is_array($templateOrTokens)) {
            throw new Exception\InvalidTokensException(sprintf(
                '%s was unable to either retrieve or compile tokens',
                __METHOD__
            ));
        }

        if ($cacheTokens) {
            $this->cachedTemplates[$template] = $templateOrTokens;
        }

        return $templateOrTokens;
    }

    /**
     * Returns an array of template name/token list pairs
     *
     * Returns an array of template name/token list pairs for all templates
     * which have been rendered by this instance. These can then be cached for
     * use with other instances, either in parallel or later.
     *
     * To seed an instance with these tokens, use {@link restoreTokens()}.
     *
     * @return array
     */
    public function getAllTokens()
    {
        return $this->cachedTemplates;
    }

    /**
     * Restore or seed this instance's list of cached template tokens
     *
     * This list should be in the form of template name/token list pairs,
     * ideally as received from {@link getAllTokens()}.
     *
     * @param  array $tokens
     * @return Mustache
     */
    public function restoreTokens(array $tokens)
    {
        $this->cachedTemplates = $tokens;
        return $this;
    }

    /**
     * Locate and retrieve a template in the template path stack
     *
     * @param  string $template
     * @return string
     * @throws Exception\TemplateNotFoundException
     */
    protected function fetchTemplate($template)
    {
        $resolver = $this->getResolver();
        $content  = $resolver->resolve($template);

        if (!$content) {
            throw new Exception\TemplateNotFoundException('Template by name "' . $template . '" not found');
        }

        return $content;
    }
}
