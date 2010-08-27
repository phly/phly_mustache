<?php

namespace Phly\Mustache;

class Renderer
{
    /**
     * Render a set of tokens with view substitutions
     * 
     * @todo   handle partials
     * @param  array $tokens 
     * @param  mixed $view 
     * @return string
     */
    public function render(array $tokens, $view)
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
                    if (empty($section)) {
                        // Section is not a true value; skip
                        $rendered .= '';
                        break;
                    }

                    // Build the section view
                    $sectionView = $section;
                    if (is_array($section)) {
                        // If an array, simply merge it with the view, giving 
                        // precedence to values in the section
                        $sectionView = array_merge($view, $section);
                    } elseif (is_object($section)) {
                        // For objects, merge in values from the view that do 
                        // not exist in the section
                        $sectionVars = array_keys(get_object_vars($section));
                        foreach ($view as $key => $value) {
                            if (!in_array($key, $sectionVars)) {
                                $section->$key = $value;
                            }
                        }
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
                    if (!empty($value)) {
                        // If a value exists for the section, we skip it
                        $rendered .= '';
                        break;
                    }

                    // Otherwise, we render it
                    $rendered .= $this->render($data['content'], $view);
                    break;
                case Lexer::TOKEN_PARTIAL:
                    /** @todo How should partials be handled internally? */
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
}
