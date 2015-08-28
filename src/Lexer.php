<?php
/**
 * phly_mustache
 *
 * @category   Phly
 * @package    phly_mustache
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache;

/**
 * Mustache Lexer
 *
 * Compiles mustache templates into a list of tokens.
 *
 * @category   Phly
 * @package    phly_mustache
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
     * Patterns referenced by lexer
     * @var array
     */
    protected $patterns = [
        'delim_start' => self::DEFAULT_DELIM_START,
        'delim_end'   => self::DEFAULT_DELIM_END,
        'varname'     => '([a-z.][a-z0-9_?.-]*|[.])',
        'pragma'      => '[A-Z][A-Z0-9_-]*',
    ];

    /**
     * The Mustache manager
     * @var Mustache
     */
    protected $manager;

    /**
     * Current nesting level in hierarchical templates
     *
     * @var int
     */
    protected $nestingLevel = 0;

    /**
     * Placeholders
     *
     * Array is keyed based on current $nestingLevel
     *
     * @var array
     */
    protected $placeholders = [];

    /**
     * Whether or not to strip whitespace
     * @var bool
     */
    protected $stripWhitespaceFlag = true;

    /**
     * Set or get the flag indicating whether or not to strip whitespace
     *
     * @param  null|bool $flag Null indicates retrieving; boolean value sets
     * @return bool|Lexer
     */
    public function disableStripWhitespace($flag = null)
    {
        if (null === $flag) {
            return !$this->stripWhitespaceFlag;
        }
        $this->stripWhitespaceFlag = !(bool) $flag;
        return $this;
    }

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
     * Compile a string into a set of tokens
     *
     * @todo   Store full matched text with each token?
     * @param  string $string
     * @param  null|string $templateName Template to use in the case of a partial
     * @return array
     * @throws Exception
     */
    public function compile($string, $templateName = null)
    {
        if (!is_string($string)) {
            throw new Exception\InvalidTemplateException();
        }

        $len     = strlen($string);

        $state   = self::STATE_CONTENT;
        $tokens  = [];
        $content = '';

        for ($i = 0; $i < $len;) {
            switch ($state) {
                case self::STATE_CONTENT:
                    $content .= $string[$i];
                    $delimStartLen = strlen($this->patterns[self::DS]);
                    if (substr($content, -$delimStartLen) === $this->patterns[self::DS]) {
                        // Create token for content
                        $tokens[] = [self::TOKEN_CONTENT, substr($content, 0, -$delimStartLen)];
                        $content  = '';

                        // Switch to tag state
                        $state = self::STATE_TAG;
                        $tagData = '';
                    }
                    ++$i;
                    break;

                case self::STATE_TAG:
                    $tagData    .= $string[$i];
                    $delimEndLen = strlen($this->patterns[self::DE]);
                    if (substr($tagData, -$delimEndLen) === $this->patterns[self::DE]) {
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
                                $tokens[] = [self::TOKEN_VARIABLE_RAW, ltrim($tagData, '{')];
                                $state    = self::STATE_CONTENT;
                                $i       += 1;
                                break;
                            case '&':
                                // Raw value start
                                $tagData = ltrim($tagData, '&');
                                $tagData = trim($tagData);

                                // Create token
                                $tokens[] = [self::TOKEN_VARIABLE_RAW, $tagData];
                                $state    = self::STATE_CONTENT;
                                $i       += 1;
                                break;
                            case '!':
                                // Comment
                                // Create token
                                $tokens[] = [self::TOKEN_COMMENT, ltrim($tagData, '!')];
                                $state    = self::STATE_CONTENT;
                                $i       += 1;
                                break;
                            case '>':
                                // Partial
                                // Trim the value of whitespace
                                $tagData = ltrim($tagData, '>');
                                $partial = trim($tagData);

                                // Create token
                                $token = [self::TOKEN_PARTIAL, [
                                    'partial' => $partial,
                                ]];

                                $tokens[] = $token;
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
                                if (!preg_match('/^=(\S+)\s+(\S+)=$/', $tagData, $matches)) {
                                    throw new Exception\InvalidDelimiterException('Did not find delimiters!');
                                }
                                $this->patterns[self::DS] = $delimStart = $matches[1];
                                $this->patterns[self::DE] = $delimEnd   = $matches[2];

                                // Create token
                                $tokens[] = [self::TOKEN_DELIM_SET, [
                                    'delim_start' => $delimStart,
                                    'delim_end'   => $delimEnd,
                                ]];
                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                            case '%':
                                // Pragmas
                                $data    = ltrim($tagData, '%');
                                $options = [];
                                if (!strstr($data, '=')) {
                                    // No options
                                    if (! preg_match(
                                        '/^(?P<pragma>' . $this->patterns['pragma'] . ')$/',
                                        $data,
                                        $matches
                                    )) {
                                        throw new Exception\InvalidPragmaNameException();
                                    }
                                    $pragma = $matches['pragma'];
                                } else {
                                    list($pragma, $options) = explode(' ', $data, 2);
                                    if (!preg_match('/^' . $this->patterns['pragma'] . '$/', $pragma)) {
                                        throw new Exception\InvalidPragmaNameException();
                                    }
                                    $pairs = explode(' ', $options);
                                    $options = [];
                                    foreach ($pairs as $pair) {
                                        if (!strstr($pair, '=')) {
                                            $options[$pair] = null;
                                        } else {
                                            list($key, $value) = explode('=', $pair, 2);
                                            $options[$key] = $value;
                                        }
                                    }
                                }
                                $tokens[] = [self::TOKEN_PRAGMA, [
                                    'pragma'  => $pragma,
                                    'options' => $options,
                                ]];
                                $state = self::STATE_CONTENT;
                                $i++;
                                break;
                            default:
                                // We have a simple variable replacement
                                if (!preg_match($this->patterns[self::VARNAME], $tagData)) {
                                    throw new Exception\InvalidVariableNameException(sprintf(
                                        'Invalid variable name provided (%s)',
                                        $tagData
                                    ));
                                }

                                // Create token
                                $tokens[] = [self::TOKEN_VARIABLE, $tagData];
                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                        }

                        break;
                    }
                    // Otherwise, we're still gathering tag data
                    ++$i;
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
                                ++$open;
                            } else {
                                ++$closed;
                            }
                        }

                        if ($closed > $open) {
                            // We're balanced if we have 1 more end tag then start tags
                            $endTag      = $ds . '/' . $section . $de;
                            $sectionData = substr($sectionData, 0, strlen($sectionData) - strlen($endTag));

                            // compile sections later
                            $tokens[] = [$tokenType, [
                                'name'     => $section,
                                'template' => $sectionData,
                            ]];
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
                $tokens[] = [self::TOKEN_CONTENT, $content];
                break;
            case self::STATE_TAG:
                // Un-closed content
                throw new Exception\UnbalancedTagException();
            case self::STATE_SECTION:
                // Un-closed section
                throw new Exception\UnbalancedSectionException(
                    'Unbalanced section, placeholder, or inheritance in template'
                );
        }

        // Tokenize any partials, sections, placeholders, or child templates
        // discovered, strip whitespaces as necessary
        $replaceKeys = [];
        foreach ($tokens as $key => $token) {
            $type = $token[0];
            switch ($type) {
                case self::TOKEN_PARTIAL:
                    // Need to grab the manager, compile the parent template
                    // (using tokenize()) referenced by the token['template'].
                    // If we have no manager, then we provide an empty token set.
                    // Additionally, if the partial name is the same as the
                    // template name provided, we should skip processing (prevents
                    // recursion).
                    if (null === ($manager = $this->getManager())
                        || $token[1]['partial'] === $templateName
                    ) {
                        break;
                    }

                    // First, reset the delimiters
                    $delimStart = $this->patterns[self::DS];
                    $delimEnd   = $this->patterns[self::DE];
                    $this->patterns[self::DS] = self::DEFAULT_DELIM_START;
                    $this->patterns[self::DE] = self::DEFAULT_DELIM_END;

                    // Tokenize the partial
                    $partial       = $token[1]['partial'];
                    $partialTokens = $manager->tokenize($partial);

                    // Restore the delimiters
                    $this->patterns[self::DS] = $delimStart;
                    $this->patterns[self::DE] = $delimEnd;

                    $token[1]['tokens'] = $partialTokens;
                    $tokens[$key]       = $token;
                    break;
                case self::TOKEN_CHILD:
                    // Need to grab the manager, compile the parent template
                    // (using tokenize()) referenced by the token['name'].
                    // If we have no manager, then we omit this section.
                    if (null === ($manager = $this->getManager())) {
                        $token[1]['content'] = '';
                        $tokens[$key] = $token;
                        break;
                    }

                    // Then, we need to compile the content (compile($token[1]['template'])).
                    // Once done, we determine what placeholders were in the content.
                    $delimStart = $this->patterns[self::DS];
                    $delimEnd   = $this->patterns[self::DE];

                    $child      = $this->compile($token[1]['template'], $templateName);

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
                    $parent = $manager->tokenize($token[1]['name'], false);
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
                        $token[1]['template'],
                        $templateName
                    );
                    $tokens[$key] = $token;

                    // Reset delimiters to retain scope
                    $this->patterns[self::DS] = $delimStart;
                    $this->patterns[self::DE] = $delimEnd;

                    // Clean whitespace
                    if (!$this->disableStripWhitespace()) {
                        $this->stripWhitespace($tokens, $key);
                    }
                    break;
                case self::TOKEN_DELIM_SET:
                    if (!$this->disableStripWhitespace()) {
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
    protected function stripWhitespace(&$tokens, $position)
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
    protected function replaceTokens(array $originalTokens, array $replacements)
    {
        $tokens = [];
        foreach ($originalTokens as $key => $token) {
            if (!array_key_exists($key, $replacements)) {
                $tokens[] = $token;
                continue;
            }

            foreach ($replacements[$key] as $replacementToken) {
                $tokens[] = $replacementToken;
            }
        }
        return $tokens;
    }
}
