<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;

/**
 * CONTEXTUAL-ESCAPE pragma
 *
 * When enabled, allows selecting an alternate escaping mechanism when rendering
 * a variable. Escapers are selected by piping the type: `varname|escaper`. Valid
 * escaping types include:
 *
 * - html (the default; does not need to be specified)
 * - attr (for escaping HTML attribute values)
 * - js (for escaping JavaScript)
 * - css (for escaping CSS)
 * - url (for escaping URLs)
 *
 * Enable the pragma as you would any other:
 *
 * <code>
 * {{%CONTEXTUAL-ESCAPE}}
 * <html>
 * <head>
 *    <script>{{scripts|js}}</script>
 *    <style>{{styles|css}}</script>
 * </head>
 * <body>
 *     <article class="{{article_class|attr}}">
 *         <a href="{{article_url|url}}">link</a>
 *     </article>
 * </body>
 * </html>
 * </code>
 */
class ContextualEscape implements PragmaInterface
{
    use PragmaNameAndTokensTrait;

    /**
     * Pragma name
     *
     * @var string
     */
    private $name = 'CONTEXTUAL-ESCAPE';

    /**
     * Tokens handled by this pragma
     * @var array
     */
    private $tokensHandled = [
        Lexer::TOKEN_VARIABLE,
    ];

    /**
     * @var string[] Valid escaping contexts.
     */
    private $validContexts = [
        'html',
        'attr',
        'js',
        'css',
        'url',
    ];

    /**
     * Escape a variable.
     *
     * If the variable does not contain piping, or the context specified is not
     * understood, returns the struct without changes.
     *
     * Otherwise, changes the data to be the value prior to the pipe, and puts
     * the context into the third element of the struct before returning it.
     *
     * @param array $tokenStruct
     * @return array
     */
    public function parse(array $tokenStruct)
    {
        if (! $tokenStruct[0] === Lexer::TOKEN_VARIABLE) {
            return $tokenStruct;
        }

        $data = $tokenStruct[1];
        if (! preg_match('/^(?P<varname>[^|]+)\|(?P<context>html|attr|js|css|url)$/', $data, $matches)) {
            return $tokenStruct;
        }

        return [
            $tokenStruct[0],
            $matches['varname'],
            $matches['context'],
        ];
    }

    /**
     * Render a given token.
     *
     * If the token is not a variable, does not contain contextual
     * information, returns null.
     *
     * If the view is scalar, escapes it using the context.
     *
     * If the view is not scalar, checks for the value in the view; if not
     * present, returns null; otherwise, escapes the view value.
     *
     * @param  array $tokenStruct
     * @param  mixed $view
     * @param  array $options
     * @param  Mustache $mustache Mustache instance handling rendering.
     * @return mixed
     */
    public function render(array $tokenStruct, $view, array $options, Mustache $mustache)
    {
        if ($tokenStruct[0] !== Lexer::TOKEN_VARIABLE) {
            return null;
        }

        if (! isset($tokenStruct[2])
            || ! in_array($tokenStruct[2], $this->validContexts, true)
        ) {
            return null;
        }

        if (is_scalar($view)) {
            return $mustache->getRenderer()->escape($view, $tokenStruct[2]);
        }

        if (is_array($view) && isset($view[$tokenStruct[1]])) {
            $value = $view[$tokenStruct[1]];
        } elseif (is_object($view) && isset($view->{$tokenStruct[1]})) {
            $value = $view->{$tokenStruct[1]};
        } elseif (is_object($view) && method_exists($view, $tokenStruct[1])) {
            $value = $view->{$tokenStruct[1]}();
        } else {
            return null;
        }

        if (is_callable($value)) {
            $value = $value();
        }

        return $mustache->getRenderer()->escape($value, $tokenStruct[2]);
    }
}
