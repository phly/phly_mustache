<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache;

use Traversable;
use Zend\Escaper\Escaper;

/**
 * Mustache renderer
 *
 * Loops through tokens, performing substitutions from the view, branching
 * based on token type and/or view values.
 */
class Renderer
{
    /**
     * @var Mustache
     */
    private $mustache;

    /**
     * Hash map of escape types to Escaper method to use
     *
     * @var array
     */
    private $escapeTypes = [
        'html' => 'escapeHtml',
        'attr' => 'escapeHtmlAttr',
        'js'   => 'escapeJs',
        'css'  => 'escapeCss',
        'url'  => 'escapeUrl',
    ];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * List of pragmas invoked by current template
     *
     * @var array
     */
    private $invokedPragmas = [];

    /**
     * @param Mustache $mustache
     * @param null|Escaper $escaper
     */
    public function __construct(Mustache $mustache, Escaper $escaper = null)
    {
        $this->mustache = $mustache;
        $this->escaper  = $escaper;
    }

    /**
     * @param  Escaper $callback
     * @return Renderer
     */
    public function setEscaper(Escaper $escaper)
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * @return Escaper
     */
    public function getEscaper()
    {
        if (! $this->escaper instanceof Escaper) {
            $this->setEscaper(new Escaper());
        }

        return $this->escaper;
    }

    /**
     * Render a set of tokens with view substitutions
     *
     * @param  array $tokens
     * @param  mixed $view
     * @param  array|null $partials
     * @return string
     */
    public function render(array $tokens, $view, array $partials = null)
    {
        // Do some pre-initialization of variables used later in the routine
        $renderer = $this;
        $inLoop   = false;

        if (is_scalar($view)) {
            // Iteration over lists will sometimes involve scalars
            $inLoop = true;
        }

        if (null === $partials) {
            $partials = [];
        }

        $rendered = '';
        foreach ($tokens as $token) {
            list($type, $data) = $token;
            if ($value = $this->handlePragmas($token, $view)) {
                $rendered .= $value;
                continue;
            }
            switch ($type) {
                case Lexer::TOKEN_CONTENT:
                    $rendered .= $data;
                    break;
                case Lexer::TOKEN_VARIABLE:
                    $value = $this->getValue($data, $view);
                    if (is_scalar($value)) {
                        if ($test = $this->handlePragmas($token, $value)) {
                            $rendered .= $test;
                            break;
                        }

                        $rendered .= ('' === $value) ? '' : $this->escape($value);
                        break;
                    }

                    $pragmaView = [$data => $value];
                    if ($test = $this->handlePragmas($token, $pragmaView)) {
                        $rendered .= $test;
                        break;
                    }

                    $rendered .= (string) $value;
                    break;
                case Lexer::TOKEN_VARIABLE_RAW:
                    $value = $this->getValue($data, $view);
                    $rendered .= $value;
                    break;
                case Lexer::TOKEN_SECTION:
                    if ($inLoop) {
                        // In a loop, with scalar values; skip
                        break;
                    }
                    $section = $this->getValue($data['name'], $view);
                    if (!$section) {
                        // Section is not a true value; skip
                        break;
                    }

                    // Build the section view
                    $sectionView = $section;
                    if (is_bool($section)) {
                        // For a boolean true, pass the current view
                        $sectionView = $view;
                    }
                    if (is_array($section) || $section instanceof Traversable) {
                        if (is_array($section) && $this->isAssocArray($section)) {
                            // Nested view; pass it as the view
                            $sectionView = $section;
                        } else {
                            // Iteration
                            $renderedSection = '';
                            foreach ($section as $sectionView) {
                                $renderedSection .= $this->render($data['content'], $sectionView);
                            }
                            $rendered .= $renderedSection;
                            break;
                        }
                    } elseif (is_callable($section) && $this->isValidCallback($section)) {
                        // Higher order section
                        // Execute the callback, passing it the section's template
                        // string, as well as a renderer lambda.
                        $mustache       = $this->mustache;
                        $invokedPragmas = $this->invokedPragmas;
                        $rendered .= call_user_func(
                            $section,
                            $data['template'],
                            function ($text) use ($renderer, $mustache, $view, $partials) {
                                $tokens = $mustache->tokenize($text);
                                return $renderer->render($tokens, $view, $partials);
                            }
                        );
                        $this->registerPragmas($invokedPragmas);
                        break;
                    } elseif (is_object($section)) {
                        // In this case, the child object is the view.
                        $sectionView = $section;
                    } else {
                        // All other types, simply pass the current view
                        $sectionView = $view;
                    }

                    // Render the section
                    $invokedPragmas = $this->invokedPragmas;
                    $rendered .= $this->render($data['content'], $sectionView);
                    $this->registerPragmas($invokedPragmas);
                    break;
                case Lexer::TOKEN_SECTION_INVERT:
                    if ($inLoop) {
                        // In a loop, with scalar values; skip
                        break;
                    }
                    $section = $this->getValue($data['name'], $view);
                    if ($section) {
                        // If a value exists for the section, we skip it
                        $rendered .= '';
                        break;
                    }

                    // Otherwise, we render it
                    $invokedPragmas = $this->invokedPragmas;
                    $rendered .= $this->render($data['content'], $view);
                    $this->registerPragmas($invokedPragmas);
                    break;
                case Lexer::TOKEN_PLACEHOLDER:
                    if ($inLoop) {
                        // In a loop, with scalar values; skip
                        break;
                    }
                    $rendered .= $this->render($data['content'], $view);
                    break;
                case Lexer::TOKEN_PARTIAL:
                    if ($inLoop) {
                        // In a loop, with scalar values; skip
                        break;
                    }
                    $invokedPragmas = $this->invokedPragmas;
                    $this->clearInvokedPragmas();
                    if (!isset($data['tokens'])) {
                        $name = $data['partial'];
                        if (isset($partials[$data['partial']])) {
                            // Partial invoked is an aliased partial
                            $rendered .= $this->render($partials[$data['partial']], $view);
                        } else {
                            $partialTokens = $this->mustache->tokenize($data['partial']);
                            $rendered .= $this->render($partialTokens, $view);
                        }
                        $this->registerPragmas($invokedPragmas);
                        break;
                    }
                    $rendered .= $this->render($data['tokens'], $view);
                    $this->registerPragmas($invokedPragmas);
                    break;
                case Lexer::TOKEN_PRAGMA:
                    $this->registerPragma($data);
                    break;
                case Lexer::TOKEN_DELIM_SET:
                case Lexer::TOKEN_COMMENT:
                default:
                    // do nothing; only necessary for tokenization/parsing
                    break;
            }
        }
        return $rendered;
    }

    /**
     * @param  string $value
     * @param  string $type Escape type to use: html, attr, js, css, url
     * @return string
     * @throws Exceptin\InvalidEscaperException for invalid escape types
     */
    public function escape($value, $type = 'html')
    {
        $escaper = $this->getEscaper();
        $type    = strtolower($type);
        $this->assertEscapeType($type);

        $method = $this->escapeTypes[$type];
        return $escaper->{$method}($value);
    }

    /**
     * Get a named value from the view
     *
     * Returns an empty string if no matching value found.
     *
     * @param  string $key
     * @param  mixed $view
     * @return mixed
     */
    public function getValue($key, $view)
    {
        if (is_scalar($view)) {
            return '';
        }

        if (strpos($key, '.')) {
            return $this->getDotValue($key, $view);
        }

        if (is_object($view)) {
            if (method_exists($view, $key)) {
                return call_user_func([$view, $key]);
            } elseif (isset($view->$key)) {
                return $view->$key;
            }
            return '';
        }
        if (isset($view[$key])) {
            if (is_callable($view[$key]) && $this->isValidCallback($view[$key])) {
                return call_user_func($view[$key]);
            }
            return $view[$key];
        }
        return '';
    }

    /**
     * De-reference a "dot value"
     *
     * A dot value indicates a variable nested in a data structure
     * in the view object.
     *
     * @param  string $key
     * @param  mixed $view
     * @return mixed
     */
    private function getDotValue($key, $view)
    {
        list($first, $second) = explode('.', $key, 2);

        $value = $this->getValue($first, $view);
        if (is_scalar($value)) {
            // To de-reference, we need a data set, not scalar data
            return '';
        }

        return $this->getValue($second, $value);
    }

    /**
     * Determine if an array is associative
     *
     * @param  array $array
     * @return bool
     */
    private function isAssocArray(array $array)
    {
        return (is_array($array)
            && (count($array) == 0
                || 0 !== count(array_diff_key($array, array_keys(array_keys($array))))
            )
        );
    }

    /**
     * Register a pragma for the current rendering session
     *
     * @param  array $definition
     */
    private function registerPragma(array $definition)
    {
        $pragmas = $this->mustache->getPragmas();
        $name    = $definition['pragma'];
        if (! $pragmas->has($name)) {
            throw new Exception\UnregisteredPragmaException(sprintf(
                'No handler for pragma "%s" registered; cannot proceed rendering',
                $name
            ));
        }
        $this->invokedPragmas[$name] = $definition['options'];
    }

    /**
     * Register cached invoked pragma definitions
     *
     * @param  array $pragmas
     */
    private function registerPragmas(array $pragmas)
    {
        $this->invokedPragmas = $pragmas;
    }

    /**
     * Clear list of invoked pragmas
     */
    private function clearInvokedPragmas()
    {
        $this->invokedPragmas = [];
    }

    /**
     * Handle pragmas
     *
     * Extend the functionality of the renderer via pragmas. When creating new
     * pragmas, extend the appropriate method for the token types affected.
     * Returning an empty value indicates that the renderer should render normally.
     *
     * This implementation includes the IMPLICIT-ITERATOR pragma, which affects
     * values within loops.
     *
     * @param  array $tokenStruct
     * @param  mixed $view
     * @return mixed
     */
    private function handlePragmas(array $tokenStruct, $view)
    {
        $mustache = $this->mustache;
        $pragmas  = $mustache->getPragmas();
        foreach ($this->invokedPragmas as $name => $options) {
            $pragma = $pragmas->get($name);
            if (! $pragma->handlesToken($tokenStruct[0])) {
                continue;
            }

            $value = $pragma->render($tokenStruct, $view, $options, $mustache);

            if ($value) {
                return $value;
            }
        }
    }

    /**
     * Is the callback provided valid?
     *
     * @param  callback $callback
     * @return bool
     */
    private function isValidCallback($callback)
    {
        // For security purposes, we don't want to call anything that isn't
        // an object callback
        if (is_string($callback)) {
            return false;
        }

        if (is_array($callback)) {
            $target = array_shift($callback);
            if (! is_object($target)) {
                return false;
            }
        }

        // Object callback -- always okay
        return true;
    }

    /**
     * Assert that a given escape type is valid.
     *
     * @param string $type
     * @throws Exceptin\InvalidEscaperException
     */
    private function assertEscapeType($type)
    {
        if (! in_array($type, array_keys($this->escapeTypes), true)) {
            throw new Exception\InvalidEscaperException('Invalid escape type provided');
        }
    }
}
