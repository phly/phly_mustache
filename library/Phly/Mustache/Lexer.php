<?php

namespace Phly\Mustache;

class Lexer
{
    // Constants referenced within lexer
    CONST DS                  = 'delim_start';
    CONST DE                  = 'delim_end';
    CONST VARNAME             = 'varname';
    const DEFAULT_DELIM_START = '{{';
    const DEFAULT_DELIM_END   = '}}';

    // State constants
    const STATE_CONTENT = 0;
    const STATE_TAG     = 1;
    const STATE_SECTION = 2;

    // Token constants
    const TOKEN_CONTENT         = 100;
    const TOKEN_VARIABLE        = 101;
    const TOKEN_VARIABLE_RAW    = 102;
    const TOKEN_COMMENT         = 103;
    const TOKEN_SECTION         = 104;
    const TOKEN_SECTION_INVERT  = 105;
    const TOKEN_PARTIAL         = 106;
    const TOKEN_DELIM_SET       = 107;
    const TOKEN_PRAGMA          = 108;

    /**
     * Patterns referenced by lexer
     * @var array
     */
    protected $patterns = array(
        'delim_start' => self::DEFAULT_DELIM_START,
        'delim_end'   => self::DEFAULT_DELIM_END,
        'varname'     => '([a-z][a-z0-9_?.-]*|[.])',
        'pragma'      => '[A-Z][A-Z0-9_-]*',
    );

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
     * Compile a string into a set of tokens
     * 
     * @todo   Store full matched text with each token?
     * @param  string $string 
     * @return array
     * @throws Exception
     */
    public function compile($string)
    {
        if (!is_string($string)) {
            throw new Exception\InvalidTemplateException();
        }

        $len     = strlen($string);

        $state   = self::STATE_CONTENT;
        $tokens  = array();
        $content = '';

        for ($i = 0; $i < $len; ) {
            switch ($state) {
                case self::STATE_CONTENT:
                    $content .= $string[$i];
                    $delimStartLen = strlen($this->patterns[self::DS]);
                    if (substr($content, -$delimStartLen) === $this->patterns[self::DS]) {
                        // Create token for content
                        $tokens[] = array(self::TOKEN_CONTENT, substr($content, 0, -$delimStartLen));
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
                                $tagData = ltrim($tagData, '#');
                                $section = trim($tagData);
                                $sectionData = '';
                                $tokenType   = self::TOKEN_SECTION;
                                $state = self::STATE_SECTION;
                                ++$i;
                                break;
                            case '^':
                                // Inverted section start
                                $tagData = ltrim($tagData, '^');
                                $section = trim($tagData);
                                $sectionData = '';
                                $tokenType   = self::TOKEN_SECTION_INVERT;
                                $state = self::STATE_SECTION;
                                ++$i;
                                break;
                            case '{':
                                // Raw value start (triple mustaches)
                                // Check that next character is a mustache; if 
                                // not, we're basically still in the tag.
                                if ($i + 1 >= $len) {
                                    // We've already reached the end of the string
                                    $tagData .= $this->patterns[self::DE];
                                    ++$i;
                                    break;
                                }
                                if ('}' !== $string[$i + 1]) {
                                    // We don't have triple mustaches yet
                                    $tagData .= $this->patterns[self::DE];
                                    ++$i;
                                    break;
                                }

                                // Advance position by one
                                ++$i;

                                // Create token
                                $tokens[] = array(self::TOKEN_VARIABLE_RAW, ltrim($tagData, '{'));
                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                            case '&':
                                // Raw value start
                                $tagData = ltrim($tagData, '&');
                                $tagData = trim($tagData);

                                // Create token
                                $tokens[] = array(self::TOKEN_VARIABLE_RAW, $tagData);
                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                            case '!':
                                // Comment
                                // Create token
                                $token = array(self::TOKEN_COMMENT, ltrim($tagData, '!'));
                                ++$i;
                                break;
                            case '>':
                                // Partial
                                // Trim the value of whitespace
                                $tagData = ltrim($tagData, '>');
                                $partial = trim($tagData);

                                // Create token
                                $token = array(self::TOKEN_PARTIAL, array(
                                    'partial' => $partial,
                                ));
                                if (null !== ($manager = $this->getManager())) {
                                    // Get the tokens for the partial
                                    $partialTokens = $manager->tokenize($partial);
                                    $token[1]['tokens'] = $partialTokens;
                                }
                                $tokens[] = $token;

                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                            case '=':
                                // Delimiter set
                                if (!$preg_match('/^=(\S+)\s+(\S+)=$/', $tagData, $matches)) {
                                    throw new Exception\InvalidDelimiterException('Did not find delimiters!');
                                }
                                $this->patterns[self::DS] = $delimStart = $matches[1];
                                $this->patterns[self::DE] = $delimEnd   = $matches[2];

                                // Create token
                                $tokens[] = array(self::TOKEN_DELIM_SET, array(
                                    'delim_start' => $delimStart,
                                    'delim_end'   => $delimEnd,
                                ));
                                $state = self::STATE_CONTENT;
                                ++$i;
                                break;
                            case '%':
                                // Pragmas
                                $data    = ltrim($tagData, '%');
                                $options = array();
                                if (!strstr($data, '=')) {
                                    // No options
                                    if (!preg_match('/^(?P<pragma>' . $this->patterns['pragma'] . ')$/', $data, $matches)) {
                                        throw new Exception\InvalidPragmaNameException();
                                    }
                                    $pragma = $matches['pragma'];
                                } else {
                                    list($pragma, $options) = explode(' ', $data, 2);
                                    if (!preg_match('/^' . $this->patterns['pragma'] . '$/', $pragma)) {
                                        throw new Exception\InvalidPragmaNameException();
                                    }
                                    $pairs = explode(' ', $options);
                                    $options = array();
                                    foreach ($pairs as $pair) {
                                        if (!strstr($pair, '=')) {
                                            $options[$pair] = null;
                                        } else {
                                            list($key, $value) = explode('=', $pair, 2);
                                            $options[$key] = $value;
                                        }
                                    }
                                }
                                $tokens[] = array(self::TOKEN_PRAGMA, array(
                                    'pragma'  => $pragma,
                                    'options' => $options,
                                ));
                                $state = self::STATE_CONTENT;
                                $i++;
                                break;
                            default:
                                // We have a simple variable replacement
                                if (!preg_match($this->patterns[self::VARNAME], $tagData)) {
                                    throw new Exception\InvalidVariableNameException('Invalid variable name provided (' . $tagData . ')');
                                }

                                // Create token
                                $tokens[] = array(self::TOKEN_VARIABLE, $tagData);
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
                            $tokens[] = array($tokenType, array(
                                'name'     => $section,
                                'template' => $sectionData,
                            ));
                            $state = self::STATE_CONTENT;
                        }
                    }

                    // Increment pointer
                    ++$i;
                    break;
                default:
                    throw new Exception\InvalidStateException('Invalid state invoked ("' . var_export($state, 1) . '")?');
            }
        }

        // Create last token
        switch ($state) {
            case self::STATE_CONTENT:
                // Un-collected content
                $tokens[] = array(self::TOKEN_CONTENT, $content);
                break;
            case self::STATE_TAG:
                // Un-closed content
                throw new Exception\UnbalancedTagException();
            case self::STATE_SECTION:
                // Un-closed section
                throw new Exception\UnbalancedSectionException('Unbalanced section in template');
        }

        // Tokenize any sections discovered
        foreach ($tokens as $key => $token) {
            $type = $token[0];
            switch ($type) {
                case self::TOKEN_SECTION:
                case self::TOKEN_SECTION_INVERT:
                    $delimStart = $this->patterns['delim_start'];
                    $delimEnd   = $this->patterns['delim_end'];

                    $token[1]['content'] = $this->compile($token[1]['template']);
                    $tokens[$key] = $token;

                    // Reset delimiters to retain scope
                    $this->patterns['delim_start'] = $delimStart;
                    $this->patterns['delim_end']   = $delimEnd;
                    break;
                default:
                    // do nothing
            }
        }
        return $tokens;
    }
}
