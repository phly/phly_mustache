<?php

namespace Phly\Mustache;

class Renderer
{
    /**
     * @var Mustache
     */
    protected $manager;

    /**
     * Set mustache manager
     *
     * Used internally to resolve and tokenize partials
     * 
     * @param  Mustache $manager 
     * @return Lexer
     */
    public function setManager(Mustache $manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Retrieve the mustache manager
     * 
     * @return null|Mustache
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Render a set of tokens with view substitutions
     * 
     * @param  array $tokens 
     * @param  mixed $view 
     * @return string
     */
    public function render(array $tokens, $view, array $partials = null)
    {
        // Do some pre-initialization of variables used later in the routine
        $renderer = $this;
        $pragmas  = array();
        $inLoop   = false;

        if (is_object($view)) {
            // If we have an object, get a list of properties and methods, 
            // giving methods precedence.
            $props = get_object_vars($view);
            foreach (get_class_methods($view) as $method) {
                if ('__' == substr($method, 0, 2)) {
                    // Omit magic methods
                    continue;
                }
                $props[$method] = array($view, $method);
            }
            $view = $props;
        }
        if (is_scalar($view)) {
            // Iteration over lists will sometimes involve scalars
            $inLoop = true;
        }

        if (null === $partials) {
            $partials = array();
        }

        $rendered = '';
        foreach ($tokens as $token) {
            list($type, $data) = $token;
            if ($pragma = $this->handlePragma($type, $data, $view, $pragmas)) {
                $rendered .= $pragma;
                continue;
            }
            switch ($type) {
                case Lexer::TOKEN_CONTENT:
                    $rendered .= $data;
                    break;
                case Lexer::TOKEN_VARIABLE:
                    $value = $this->getValue($data, $view);
                    $value = ('' === $value) ? '' : $this->escape($value);
                    $rendered .= $value;
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
                    if (is_array($section) || $section instanceof \Traversable) {
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
                    } elseif (is_callable($section)) {
                        // Higher order section
                        // Execute the callback, passing it the section's template 
                        // string, as well as a renderer lambda.
                        $rendered .= call_user_func($section, $data['template'], function($text) use ($renderer, $view, $partials) {
                            $manager = $renderer->getManager();
                            if (!$manager instanceof Mustache) {
                                return $text;
                            }
                            $tokens = $manager->tokenize($text);
                            return $renderer->render($tokens, $view, $partials);
                        });
                        break;
                    } elseif (is_object($section)) {
                        // In this case, the child object is the view.
                        $sectionView = $section;
                    } else {
                        // All other types, simply pass the current view
                        $sectionView = $view;
                    }

                    // Render the section
                    $rendered .= $this->render($data['content'], $sectionView);
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
                    $rendered .= $this->render($data['content'], $view);
                    break;
                case Lexer::TOKEN_PARTIAL:
                    if ($inLoop) {
                        // In a loop, with scalar values; skip
                        break;
                    }
                    if (!isset($data['tokens'])) {
                        // Check to see if the partial invoked is an aliased partial
                        $name = $data['partial'];
                        if (isset($partials[$data['partial']])) {
                            $rendered .= $this->render($partials[$data['partial']], $view);
                        }
                        break;
                    }
                    $rendered .= $this->render($data['tokens'], $view);
                    break;
                case Lexer::TOKEN_PRAGMA:
                    $this->registerPragma($data, $pragmas);
                    break;
                case Lexer::TOKEN_DELIM_SET:
                case Lexer::COMMENT:
                default:
                    // do nothing; only necessary for tokenization/parsing
                    break;
            }
        }
        return $rendered;
    }

    /**
     * escape 
     * 
     * @todo   allow using a callback for escaping
     * @param  string $value 
     * @return string
     */
    public function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8');
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
    protected function getValue($key, $view)
    {
        if (is_scalar($view)) {
            return $view;
        }
        if (is_object($view)) {
            if (isset($view->$key)) {
                if (is_callable($view->$key)) {
                    return call_user_func($view->$key);
                }
            }
            return '';
        }
        if (isset($view[$key])) {
            if (is_callable($view[$key])) {
                return call_user_func($view[$key]);
            }
            return $view[$key];
        } 
        return '';
    }

    /**
     * Determine if an array is associative
     * 
     * @param  array $array 
     * @return bool
     */
    protected function isAssocArray(array $array)
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
     * @param  array $pragmas
     * @return void
     */
    protected function registerPragma(array $definition, array &$pragmas)
    {
        $pragmas[$definition['pragma']] = $definition['options'];
    }

    /**
     * Does a given pragma exist?
     * 
     * @param  string $pragma 
     * @param  array $pragmas 
     * @return bool
     */
    protected function hasPragma($pragma, array $pragmas)
    {
        return array_key_exists($pragma, $pragmas);
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
     * @param  string $token 
     * @param  mixed $data 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return mixed
     */
    public function handlePragma($token, $data, $view, $pragmas)
    {
        switch ($token) {
            case Lexer::TOKEN_CONTENT:
                return $this->handleContentPragmas($data, $view, $pragmas);
            case Lexer::TOKEN_VARIABLE:
                return $this->handleVariablePragmas($data, $view, $pragmas);
            case Lexer::TOKEN_VARIABLE_RAW:
                return $this->handleRawVariablePragmas($data, $view, $pragmas);
            case Lexer::TOKEN_SECTION:
                return $this->handleSectionPragmas($data, $view, $pragmas);
            case Lexer::TOKEN_SECTION_INVERT:
                return $this->handleInvertedSectionPragmas($data, $view, $pragmas);
        }
    }

    /**
     * Handle TOKEN_CONTENT pragmas
     * 
     * @param  string $content 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return mixed
     */
    public function handleContentPragmas($content, $view, array $pragmas)
    {
    }

    /**
     * Handle TOKEN_VARIABLE pragmas
     *
     * This implements the IMPLICIT-ITERATOR pragma.
     * 
     * @param  string $variable 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return mixed
     */
    public function handleVariablePragmas($variable, $view, array $pragmas)
    {
        if (!$this->hasPragma('IMPLICIT-ITERATOR', $pragmas)) {
            return;
        }
        $options = $pragmas['IMPLICIT-ITERATOR'];
        $iterator = isset($options['iterator']) ? $options['iterator'] : '.';
        if ($iterator == $variable) {
            return $this->escape($view);
        }
    }

    /**
     * Handle TOKEN_VARIABLE_RAW pragmas
     * 
     * This implements the IMPLICIT-ITERATOR pragma.
     * 
     * @param  string $variable 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return void
     */
    public function handleRawVariablePragmas($variable, $view, array $pragmas)
    {
        if (!$this->hasPragma('IMPLICIT-ITERATOR', $pragmas)) {
            return;
        }
        if (!is_scalar($view)) {
            return;
        }
        $options = $pragmas['IMPLICIT-ITERATOR'];
        $iterator = isset($options['iterator']) ? $options['iterator'] : '.';
        if ($iterator == $variable) {
            return $view;
        }
    }

    /**
     * Handle TOKEN_SECTION pragmas
     * 
     * @param  array $section 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return mixed
     */
    public function handleSectionPragmas($section, $view, array $pragmas)
    {
    }

    /**
     * Handle TOKEN_SECTION_INVERT pragmas
     * 
     * @param  array $section 
     * @param  mixed $view 
     * @param  array $pragmas 
     * @return mixed
     */
    public function handleInvertedSectionPragmas($section, $view, array $pragmas)
    {
    }
}
