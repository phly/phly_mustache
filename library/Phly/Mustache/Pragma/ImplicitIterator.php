<?php

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Lexer;

class ImplicitIterator extends AbstractPragma
{
    protected $name = 'IMPLICIT-ITERATOR';

    protected $tokensHandled = array(
        Lexer::TOKEN_VARIABLE,
        Lexer::TOKEN_VARIABLE_RAW,
    );

    /**
     * Handle a given token
     *
     * Returning an empty value returns control to the renderer.
     * 
     * @param  int $token 
     * @param  mixed $data 
     * @param  mixed $view 
     * @param  array $options 
     * @return mixed
     */
    public function handle($token, $data, $view, array $options)
    {
        // If we don't have a scalar view, implicit iteration isn't possible
        if (!is_scalar($view)) {
            return;
        }

        // Do we escape?
        $escape = true;
        switch ($token) {
            case Lexer::TOKEN_VARIABLE:
                // Yes
                break;
            case Lexer::TOKEN_VARIABLE_RAW:
                // No
                $escape = false;
                break;
            default:
                // Wrong token type! Just return;
                return;
        }

        // Get the iterator option, and compare it to the token we received
        $iterator = isset($options['iterator']) ? $options['iterator'] : '.';
        if ($iterator === $data) {
            // Match found, so replace the value
            return ($escape) ? $this->getRenderer()->escape($view) : $view;
        }
    }
}
