<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache;

/**
 * Mustache Lexer
 *
 * Compiles mustache templates into a list of tokens.
 */
class Lexer
{
    /**@+
     * Constants referenced within lexer
     * @var string
     */
    const DS                  = 'delim_start';
    const DE                  = 'delim_end';
    const VARNAME             = 'varname';
    const DEFAULT_DELIM_START = '{{';
    const DEFAULT_DELIM_END   = '}}';
    /**@-*/

    /**@+
     * Constants referencing lexing states
     * @var int
     */
    const STATE_CONTENT     = 0;
    const STATE_TAG         = 1;
    const STATE_SECTION     = 2;
    /**@-*/

    /**@+
     * Constants referencing token types
     * @var int
     */
    const TOKEN_CONTENT         = 100;
    const TOKEN_VARIABLE        = 101;
    const TOKEN_VARIABLE_RAW    = 102;
    const TOKEN_COMMENT         = 103;
    const TOKEN_SECTION         = 104;
    const TOKEN_SECTION_INVERT  = 105;
    const TOKEN_PARTIAL         = 106;
    const TOKEN_DELIM_SET       = 107;
    const TOKEN_PRAGMA          = 108;
    const TOKEN_PLACEHOLDER     = 109;
    const TOKEN_CHILD           = 110;
    /**@-*/

    /**
     * Patterns referenced by lexer.
     *
     * @var array
     */
    private $patterns = [
        'delim_start' => self::DEFAULT_DELIM_START,
        'delim_end'   => self::DEFAULT_DELIM_END,
        'varname'     => '([a-z.][a-z0-9_?.-]*|[.])',
        'pragma'      => '[A-Z][A-Z0-9_-]*',
    ];

    /**
     * Current nesting level in hierarchical templates
     *
     * @var int
     */
    private $nestingLevel = 0;

    /**
     * Placeholders
     *
     * Array is keyed based on current $nestingLevel
     *
     * @var array
     */
    private $placeholders = [];

    /**
     * Whether or not to strip whitespace
     * @var bool
     */
    private $stripWhitespaceFlag = true;

    /**
     * Allowed tokens (used for validating returns from pragmas)
     *
     * @var int[]
     */
    private $validTokens = [
        self::TOKEN_CONTENT,
        self::TOKEN_VARIABLE,
        self::TOKEN_VARIABLE_RAW,
        self::TOKEN_COMMENT,
        self::TOKEN_SECTION,
        self::TOKEN_SECTION_INVERT,
        self::TOKEN_PARTIAL,
        self::TOKEN_DELIM_SET,
        self::TOKEN_PRAGMA,
        self::TOKEN_PLACEHOLDER,
        self::TOKEN_CHILD,
    ];

    /**
     * Set or get the flag indicating whether or not to strip whitespace
     *
     * @param  null|bool $flag Null indicates retrieving; boolean value sets
     * @return bool|Lexer
     */
    public function disableStripWhitespace($flag = null)
    {
        if (null === $flag) {
            return ! $this->stripWhitespaceFlag;
        }
        $this->stripWhitespaceFlag = ! (bool) $flag;
        return $this;
    }

    /**
     * Compile a string into a set of tokens.
     *
     * @todo   Store full matched text with each token?
     * @param  Mustache $mustache Mustache instance invoking compilation.
     * @param  string $string
     * @param  null|string $templateName Template to use in the case of a
     *     partial; can be null.
     * @param  array $scopedPragmas List of pragmas currently in scope; should
     *     only be used internally.
     * @return array
     * @throws Exception
     */
    public function compile(Mustache $mustache, $string, $templateName = null, $scopedPragmas = [])
    {
        if (! is_string($string)) {
            throw new Exception\InvalidTemplateException();
        }

        $len           = strlen($string);
        $delimStartLen = strlen($this->patterns[self::DS]);
        $delimEndLen   = strlen($this->patterns[self::DE]);

        $state         = self::STATE_CONTENT;
        $pragmas       = $mustache->getPragmas();
        $tokens        = [];
        $content       = '';
        $tagData       = '';

        for ($i = 0; $i < $len;) {
            switch ($state) {
                case self::STATE_CONTENT:
                    $content .= $string[$i];
                    if (substr($content, -$delimStartLen) !== $this->patterns[self::DS]) {
                        // No start delimiter found in content yet; continue to next character.
                        $i += 1;
                        break;
                    }

                    // Create token for content
                    $tokens[] = $this->parseViaPragmas(
                        [self::TOKEN_CONTENT, substr($content, 0, -$delimStartLen)],
                        $pragmas,
                        $scopedPragmas
                    );
                    $content  = '';

                    // Switch to tag state
                    $state   = self::STATE_TAG;
                    $tagData = '';

                    $i += 1;
                    break;

                case self::STATE_TAG:
                    $tagData .= $string[$i];
                    if (substr($tagData, -$delimEndLen) !== $this->patterns[self::DE]) {
                        // Have not reached end of tag delimiter
                        $i += 1;
                        break;
                    }

                    // End of tag reached; start processing.
                    $tagData = substr($tagData, 0, -$delimEndLen);

                    // Evaluate what kind of token we have
                    switch ($tagData[0]) {
                        case '#':
                            // Section start
                            $tagData     = ltrim($tagData, '#');
                            $section     = trim($tagData);
                            $sectionData = '';
                            $tokenType   = self::TOKEN_SECTION;
                            $state       = self::STATE_SECTION;
                            $i          += 1;
                            break;

                        case '$':
                            // Placeholder start
                            //
                            // Placeholders are captured as a set of tokens. They are
                            // essentially a type of section.
                            //
                            // In a child template, any placeholders defined are then
                            // replaced with the tokens they contain.
                            $tagData     = ltrim($tagData, '$');
                            $section     = trim($tagData);
                            $sectionData = '';
                            $tokenType   = self::TOKEN_PLACEHOLDER;
                            $state       = self::STATE_SECTION;
                            $i          += 1;
                            break;

                        case '^':
                            // Inverted section start
                            $tagData     = ltrim($tagData, '^');
                            $section     = trim($tagData);
                            $sectionData = '';
                            $tokenType   = self::TOKEN_SECTION_INVERT;
                            $state       = self::STATE_SECTION;
                            $i          += 1;
                            break;

                        case '{':
                            // Raw value start (triple mustaches)
                            // Check that next character is a mustache; if
                            // not, we're basically still in the tag.
                            if ($i + 1 >= $len) {
                                // We've already reached the end of the string
                                $tagData .= $this->patterns[self::DE];
                                $i       += 1;
                                break;
                            }
                            if ('}' !== $string[$i + 1]) {
                                // We don't have triple mustaches yet
                                $tagData .= $this->patterns[self::DE];
                                $i       += 1;
                                break;
                            }

                            // Advance position by one
                            $i += 1;

                            // Create token
                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_VARIABLE_RAW, ltrim($tagData, '{')],
                                $pragmas,
                                $scopedPragmas
                            );
                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;

                        case '&':
                            // Raw value start
                            $tagData = ltrim($tagData, '&');
                            $tagData = trim($tagData);

                            // Create token
                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_VARIABLE_RAW, $tagData],
                                $pragmas,
                                $scopedPragmas
                            );
                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;

                        case '!':
                            // Comment
                            // Create token
                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_COMMENT, ltrim($tagData, '!')],
                                $pragmas,
                                $scopedPragmas
                            );
                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;

                        case '>':
                            // Partial
                            // Trim the value of whitespace
                            $tagData = ltrim($tagData, '>');
                            $partial = trim($tagData);

                            // Create token
                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_PARTIAL, [ 'partial' => $partial, ]],
                                $pragmas,
                                $scopedPragmas
                            );

                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;

                        case '<':
                            // Template inheritance
                            //
                            // Indicates that the content provides placeholders for
                            // the inherited template. The parent template is parsed,
                            // and then the content is parsed for placeholders. Any
                            // placeholders found are then used to replace placeholders
                            // of the same name in the parent template. The content
                            // is then replaced with the parent tokens.
                            //
                            // For purposes of first-pass lexing, it's a type of section.
                            $tagData     = ltrim($tagData, '<');
                            $section     = trim($tagData);
                            $sectionData = '';
                            $tokenType   = self::TOKEN_CHILD;
                            $state       = self::STATE_SECTION;
                            $i          += 1;
                            break;

                        case '=':
                            // Delimiter set
                            if (! preg_match('/^=(\S+)\s+(\S+)=$/', $tagData, $matches)) {
                                throw new Exception\InvalidDelimiterException('Did not find delimiters!');
                            }
                            $this->patterns[self::DS] = $delimStart = $matches[1];
                            $this->patterns[self::DE] = $delimEnd   = $matches[2];

                            // Create token
                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_DELIM_SET, [
                                    'delim_start' => $delimStart,
                                    'delim_end'   => $delimEnd,
                                ]],
                                $pragmas,
                                $scopedPragmas
                            );
                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;
                        case '%':
                            // Pragmas
                            $data    = ltrim($tagData, '%');
                            if (! preg_match(
                                '/^(?P<pragma>' . $this->patterns['pragma'] . ')( (?P<options>.*))?$/',
                                $data,
                                $matches
                            )) {
                                throw new Exception\InvalidPragmaNameException();
                            }

                            $pragma  = $matches['pragma'];
                            $options = [];

                            if (! empty($matches['options'])) {
                                $pairs = explode(' ', $matches['options']);
                                foreach ($pairs as $pair) {
                                    if (! strstr($pair, '=')) {
                                        $options[$pair] = null;
                                        continue;
                                    }

                                    list($key, $value) = explode('=', $pair, 2);
                                    $options[$key] = $value;
                                }
                            }

                            $tokens[] = $this->parseViaPragmas(
                                [self::TOKEN_PRAGMA, [
                                    'pragma'  => $pragma,
                                    'options' => $options,
                                ]],
                                $pragmas,
                                $scopedPragmas
                            );

                            $scopedPragmas[] = $pragma;
                            $state           = self::STATE_CONTENT;
                            $i              += 1;
                            break;

                        default:
                            // We have a simple variable replacement

                            // First, create the token, passing it to pragmas; this allows pragmas
                            // to filter the variable name if required.
                            $token = $this->parseViaPragmas(
                                [self::TOKEN_VARIABLE, $tagData],
                                $pragmas,
                                $scopedPragmas
                            );

                            // Now filter the tag data (index 1 of the token) to ensure it is valid.
                            if (! preg_match($this->patterns[self::VARNAME], $token[1])) {
                                throw new Exception\InvalidVariableNameException(sprintf(
                                    'Invalid variable name provided (%s)',
                                    $token[1]
                                ));
                            }

                            // Add the token to the list, and continue.
                            $tokens[] = $token;
                            $state    = self::STATE_CONTENT;
                            $i       += 1;
                            break;
                    }

                    break;

                case self::STATE_SECTION:
                    $sectionData .= $string[$i];
                    $ds           = $this->patterns[self::DS];
                    $de           = $this->patterns[self::DE];
                    $pattern      = $ds . '/' . $section . $de;
                    if (preg_match('/' . preg_quote($pattern, '/') . '$/', $sectionData)) {
                        // we have a match. Now, let's make sure we're balanced
                        $pattern = '/(('
                                 . preg_quote($ds . '#' . $section . $de, '/')
                                 . ')|('
                                 . preg_quote($ds . '/' . $section . $de, '/')
                                 . '))/';
                        preg_match_all($pattern, $sectionData, $matches);
                        $open   = 0;
                        $closed = 0;
                        foreach ($matches[3] as $match) {
                            if ('' === $match) {
                                $open += 1;
                            } else {
                                $closed += 1;
                            }
                        }

                        if ($closed > $open) {
                            // We're balanced if we have 1 more end tag then start tags
                            $endTag      = $ds . '/' . $section . $de;
                            $sectionData = substr($sectionData, 0, strlen($sectionData) - strlen($endTag));

                            // compile sections later
                            $tokens[] = $this->parseViaPragmas(
                                [$tokenType, [
                                    'name'     => $section,
                                    'template' => $sectionData,
                                ]],
                                $pragmas,
                                $scopedPragmas
                            );
                            $state = self::STATE_CONTENT;
                        }
                    }

                    // Increment pointer
                    $i += 1;
                    break;

                default:
                    throw new Exception\InvalidStateException(sprintf(
                        'Invalid state invoked ("%s")?',
                        var_export($state, 1)
                    ));
            }
        }

        // Create last token
        switch ($state) {
            case self::STATE_CONTENT:
                // Un-collected content
                $tokens[] = $this->parseViaPragmas([self::TOKEN_CONTENT, $content], $pragmas, $scopedPragmas);
                break;

            case self::STATE_TAG:
                // Un-closed content
                throw new Exception\UnbalancedTagException();

            case self::STATE_SECTION:
                // Un-closed section
                throw new Exception\UnbalancedSectionException(
                    'Unbalanced section, placeholder, or inheritance in template'
                );

            default:
                // Do nothing.
        }

        // Tokenize any partials, sections, placeholders, or child templates
        // discovered, strip whitespaces as necessary
        $replaceKeys = [];
        foreach ($tokens as $key => $token) {
            $type = $token[0];
            switch ($type) {
                case self::TOKEN_PARTIAL:
                    // If the partial name is the same as the template name
                    // provided, we should skip processing (prevents
                    // recursion).
                    if ($token[1]['partial'] === $templateName) {
                        break;
                    }

                    // First, reset the delimiters
                    $delimStart = $this->patterns[self::DS];
                    $delimEnd   = $this->patterns[self::DE];
                    $this->patterns[self::DS] = self::DEFAULT_DELIM_START;
                    $this->patterns[self::DE] = self::DEFAULT_DELIM_END;

                    // Tokenize the partial
                    $partial       = $token[1]['partial'];
                    $partialTokens = $mustache->tokenize($partial);

                    // Restore the delimiters
                    $this->patterns[self::DS] = $delimStart;
                    $this->patterns[self::DE] = $delimEnd;

                    $token[1]['tokens'] = $partialTokens;
                    $tokens[$key]       = $token;
                    break;

                case self::TOKEN_CHILD:
                    // We need to compile the content (compile($token[1]['template'])).
                    // Once done, we determine what placeholders were in the content.
                    $delimStart = $this->patterns[self::DS];
                    $delimEnd   = $this->patterns[self::DE];

                    $child      = $this->compile($mustache, $token[1]['template'], $templateName);

                    // Reset delimiters to retain scope
                    $this->patterns[self::DS] = $delimStart;
                    $this->patterns[self::DE] = $delimEnd;

                    // Get placeholders from child
                    $placeholders = [];
                    foreach ($child as $childToken) {
                        $childType = $childToken[0];
                        if ($childType !== self::TOKEN_PLACEHOLDER) {
                            continue;
                        }
                        $placeholders[$childToken[1]['name']] = $childToken[1]['content'];
                    }

                    // Now, tokenize the parent
                    $this->nestingLevel += 1;
                    $this->placeholders[$this->nestingLevel] = $placeholders;
                    $parent = $mustache->tokenize($token[1]['name'], false);
                    unset($this->placeholders[$this->nestingLevel]);
                    $this->nestingLevel -= 1;

                    // At this point, we hint that we need to remove the
                    // previous token, and inject the tokens of the parent in
                    // sequence.
                    $replaceKeys[$key] = $parent;
                    break;

                case self::TOKEN_PLACEHOLDER:
                    if ($this->nestingLevel > 0) {
                        $placeholders = [];
                        foreach ($this->placeholders as $childPlaceholders) {
                            // 1 is deepest, and thus has precedence
                            $placeholders = array_merge($childPlaceholders, $placeholders);
                        }

                        $placeholder = $token[1]['name'];

                        if (isset($placeholders[$placeholder])) {
                            $token[1]['content'] = $placeholders[$placeholder];
                            $tokens[$key]        = $token;

                            // Break out of the case; we had a replacement
                            break;
                        }
                    }
                    // intentionally fall through in the case of no nesting level
                case self::TOKEN_SECTION:
                case self::TOKEN_SECTION_INVERT:
                    $delimStart = $this->patterns[self::DS];
                    $delimEnd   = $this->patterns[self::DE];

                    $token[1]['content'] = $this->compile(
                        $mustache,
                        $token[1]['template'],
                        $templateName,
                        $scopedPragmas
                    );
                    $tokens[$key] = $token;

                    // Reset delimiters to retain scope
                    $this->patterns[self::DS] = $delimStart;
                    $this->patterns[self::DE] = $delimEnd;

                    // Clean whitespace
                    if (! $this->disableStripWhitespace()) {
                        $this->stripWhitespace($tokens, $key);
                    }
                    break;

                case self::TOKEN_DELIM_SET:
                    if (! $this->disableStripWhitespace()) {
                        $this->stripWhitespace($tokens, $key);
                    }
                    break;

                default:
                    // do nothing
            }
        }

        if (count($replaceKeys)) {
            $tokens = $this->replaceTokens($tokens, $replaceKeys);
        }

        return $tokens;
    }

    /**
     * Strip whitespace in content tokens surrounding a given token
     *
     * @param  ref $tokens Reference to the tokens array
     * @param  int $position
     * @return void
     */
    private function stripWhitespace(&$tokens, $position)
    {
        switch ($tokens[$position][0]) {
            case self::TOKEN_PLACEHOLDER:
            case self::TOKEN_SECTION:
            case self::TOKEN_SECTION_INVERT:
                // Analyze first token of section, and strip leading newlines
                $sectionTokens = $tokens[$position][1]['content'];
                if (0 === count($sectionTokens)) {
                    break;
                }
                $token = $sectionTokens[0];
                if ($token[0] === self::TOKEN_CONTENT) {
                    $content = preg_replace('/^\s*?(\r\n?|\n)/s', '', $token[1]);
                    $token = [
                        self::TOKEN_CONTENT,
                        $content,
                        'original_content' => $token[1],
                    ];
                    $sectionTokens[0] = $token;
                    $tokens[$position][1]['content'] = $sectionTokens;
                    break;
                }
                break;
            default:
                break;
        }

        // Analyze preceding token; if content token, and ending with a newline
        // and optionally whitespace, trim the whitespace.
        if (($position - 1) > -1) {
            $previous = $tokens[$position - 1];
            $type = $previous[0];
            if ($type === self::TOKEN_CONTENT) {
                $content = preg_replace('/(\r\n?|\n)\s+$/s', '$1', $previous[1]);
                $previous = [
                    self::TOKEN_CONTENT,
                    $content,
                    'original_content' => $previous[1],
                ];
                $tokens[$position - 1] = $previous;
            }
        }

        // Analyze next token. If it is a content token, and begins with optional
        // whitespace, followed by a newline, trim this whitespace.
        if (isset($tokens[$position + 1])) {
            $next = $tokens[$position + 1];
            $type = $next[0];
            if ($type === self::TOKEN_CONTENT) {
                $content = preg_replace('/^\s*?(\r\n?|\n)/s', '', $next[1]);
                $next = [
                    self::TOKEN_CONTENT,
                    $content,
                    'original_content' => $next[1],
                ];
                $tokens[$position + 1] = $next;
            }
        }
    }

    /**
     * Inject replacements from template inheritance
     *
     * @param  array $originalTokens
     * @param  array $replacements
     * @return array
     */
    private function replaceTokens(array $originalTokens, array $replacements)
    {
        $tokens = [];
        foreach ($originalTokens as $key => $token) {
            if (! array_key_exists($key, $replacements)) {
                $tokens[] = $token;
                continue;
            }

            foreach ($replacements[$key] as $replacementToken) {
                $tokens[] = $replacementToken;
            }
        }
        return $tokens;
    }

    /**
     * Parse a token via a pragma.
     *
     * Passes the token and data to each pragma capable of handling the given token;
     * pragmas are expected to return a valid token struct (array with token and data).
     *
     * Each pragma is passed the token data as returned by the previous pragma, which
     * means that the order in which pragmas are registered can matter.
     */
    private function parseViaPragmas(array $tokenStruct, Pragma\PragmaCollection $pragmas, array $scopedPragmas)
    {
        $token = $tokenStruct[0];
        foreach ($this->filterPragmasByToken($token, $pragmas, $scopedPragmas) as $pragma) {
            $data        = $tokenStruct[1];
            $tokenStruct = $pragma->parse($tokenStruct);
            $this->assertTokenStruct($tokenStruct);
        }

        return $tokenStruct;
    }

    /**
     * Generator for filtering pragmas by those matching a given token.
     *
     * @param string $token
     * @param Pragma\PragmaCollection $pragmas
     * @return Pragma\PragmaInterface
     */
    private function filterPragmasByToken($token, Pragma\PragmaCollection $pragmas, array $scopedPragmas)
    {
        foreach ($pragmas as $pragma) {
            if (in_array($pragma->getName(), $scopedPragmas, true)
                && $pragma->handlesToken($token)
            ) {
                yield $pragma;
            }
        }
    }

    /**
     * Assert that a token struct is valid.
     *
     * @throws Exception\InvalidTokenException for any invalid token structures or elements.
     */
    private function assertTokenStruct($tokenStruct)
    {
        if (! is_array($tokenStruct)) {
            throw new Exception\InvalidTokenException('Invalid token struct; must be an array');
        }

        if (2 > count($tokenStruct)) {
            throw new Exception\InvalidTokenException('Invalid token struct; missing data');
        }

        if (! isset($tokenStruct[0]) || ! isset($tokenStruct[1])) {
            throw new Exception\InvalidTokenException('Invalid token struct; missing either index 0 or 1');
        }

        if (! in_array($tokenStruct[0], $this->validTokens, true)) {
            throw new Exception\InvalidTokenException('Invalid token struct; invalid token at position 0');
        }
    }
}
