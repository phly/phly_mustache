<?php

namespace Phly\Mustache;

class Renderer
{
    /**
     * Render a set of tokens with view substitutions
     * 
     * @param  array $tokens 
     * @param  mixed $view 
     * @return string
     */
    public function render(array $tokens, $view, array $partials = null)
    {
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
        if (!is_array($view)) {
            throw new \Exception('Invalid view provided; must be an array or object, received ' . gettype($view));
        }

        if (null === $partials) {
            $partials = array();
        }

        $rendered = '';
        foreach ($tokens as $token) {
            list($type, $data) = $token;
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
                    $section = $this->getValue($data['name'], $view);
                    if (!$section) {
                        // Section is not a true value; skip
                        $rendered .= '';
                        break;
                    }

                    // Build the section view
                    $sectionView = $section;
                    if (is_bool($section)) {
                        // For a boolean true, pass the current view
                        $sectionView = $view;
                    }
                    if (is_array($section)) {
                        if ($this->isAssocArray($section)) {
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
                        /** @todo Not sure how to handle higher order sections;
                         *        Supposedly, should pass a renderer and text, but
                         *        that means no view is passed, which will cause 
                         *        issues. Half thinking just pass the rendered 
                         *        template would be sufficient.
                         */
                        // Higher order section; execute the callback, and use the
                        // returned string.
                        $rendered .= call_user_func($section, $data['content'], array($this, 'render'));
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
     * @param  array $view 
     * @return mixed
     */
    protected function getValue($key, array $view)
    {
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
}
