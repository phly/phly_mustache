<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Pragma;

use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma;
use PHPUnit_Framework_TestCase as TestCase;

class ImplicitIteratorTest extends TestCase
{
    public function setUp()
    {
        $this->pragma = new Pragma\ImplicitIterator;
    }

    public function validTokens()
    {
        return [
            'variable'     => [Lexer::TOKEN_VARIABLE],
            'raw-variable' => [Lexer::TOKEN_VARIABLE_RAW],
        ];
    }

    /**
     * @dataProvider validTokens
     */
    public function testHandlesVariables($token)
    {
        $this->assertTrue($this->pragma->handlesToken($token));
    }

    public function invalidTokens()
    {
        return [
            'TOKEN_CONTENT'        => [Lexer::TOKEN_CONTENT],
            'TOKEN_COMMENT'        => [Lexer::TOKEN_COMMENT],
            'TOKEN_SECTION'        => [Lexer::TOKEN_SECTION],
            'TOKEN_SECTION_INVERT' => [Lexer::TOKEN_SECTION_INVERT],
            'TOKEN_PARTIAL'        => [Lexer::TOKEN_PARTIAL],
            'TOKEN_DELIM_SET'      => [Lexer::TOKEN_DELIM_SET],
            'TOKEN_PRAGMA'         => [Lexer::TOKEN_PRAGMA],
            'TOKEN_PLACEHOLDER'    => [Lexer::TOKEN_PLACEHOLDER],
            'TOKEN_CHILD'          => [Lexer::TOKEN_CHILD],
        ];
    }

    /**
     * @dataProvider invalidTokens
     */
    public function testDoesNotHandleNonVariableTokens($token)
    {
        $this->assertFalse($this->pragma->handlesToken($token));
    }

    public function testHandleEscapesNormalVariable()
    {
        $mustache = new Mustache();
        $result   = $this->pragma->render(
            Lexer::TOKEN_VARIABLE,
            '.',
            '<b>foo</b>',
            [],
            $mustache
        );

        $this->assertEquals($mustache->getRenderer()->escape('<b>foo</b>'), $result);
    }

    public function testHandlePassesThroughRawVariable()
    {
        $mustache = new Mustache();
        $result   = $this->pragma->render(
            Lexer::TOKEN_VARIABLE_RAW,
            '.',
            '<b>foo</b>',
            [],
            $mustache
        );

        $this->assertEquals('<b>foo</b>', $result);
    }

    /**
     * @dataProvider invalidTokens
     */
    public function testHandleReturnsEarlyForUnrecognizedTokens($token)
    {
        $mustache = new Mustache();
        $this->assertNull($this->pragma->render(
            $token,
            '.',
            '<b>foo</b>',
            [],
            $mustache
        ));
    }

    public function nonScalarViews()
    {
        foreach ($this->validTokens() as $token) {
            foreach ([
                ['foo'],
                ['foo' => 'bar'],
                (object) ['foo' => 'bar'],
            ] as $view) {
                yield [$token, $view];
            }
        }
    }

    /**
     * @dataProvider nonScalarViews
     */
    public function testHandleReturnsEarlyForNonScalarViews($token, $view)
    {
        $mustache = new Mustache();
        $this->assertNull($this->pragma->render(
            $token,
            '.',
            $view,
            [],
            $mustache
        ));
    }

    public function testRendersVariableIfDataMatchesIteratorOption()
    {
        $mustache = new Mustache();
        $result   = $this->pragma->render(
            Lexer::TOKEN_VARIABLE,
            'foo',
            '<b>foo</b>',
            ['iterator' => 'foo'],
            $mustache
        );

        $this->assertEquals($mustache->getRenderer()->escape('<b>foo</b>'), $result);
    }

    public function testReturnsEarlyIfDataDoesNotMatchIteratorOption()
    {
        $mustache = new Mustache();
        $this->assertNull($this->pragma->render(
            Lexer::TOKEN_VARIABLE,
            '.',
            '<b>foo</b>',
            ['iterator' => 'foo'],
            $mustache
        ));
    }

    public function testReturnsEarlyIfDataDoesNotMatchDefaultIteratorSequence()
    {
        $mustache = new Mustache();
        $this->assertNull($this->pragma->render(
            Lexer::TOKEN_VARIABLE,
            'foo',
            '<b>foo</b>',
            [],
            $mustache
        ));
    }
}
