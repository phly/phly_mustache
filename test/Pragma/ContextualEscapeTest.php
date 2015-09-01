<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Pragma;

use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma;
use Phly\Mustache\Renderer;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Escaper\Escaper;

class ContextualEscapeTest extends TestCase
{
    public function setUp()
    {
        $this->escaper  = new Escaper();
        $this->mustache = new Mustache();
        $this->renderer = new Renderer($this->mustache);
        $this->mustache->setRenderer($this->renderer);

        $this->pragma   = new Pragma\ContextualEscape;
    }

    public function testProvidesPragmaName()
    {
        $this->assertEquals('CONTEXTUAL-ESCAPE', $this->pragma->getName());
    }

    public function validTokens()
    {
        return [
            'variable'     => [Lexer::TOKEN_VARIABLE],
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
            'TOKEN_VARIABLE_RAW'   => [Lexer::TOKEN_VARIABLE_RAW],
        ];
    }

    /**
     * @dataProvider invalidTokens
     */
    public function testDoesNotHandleNonVariableTokens($token)
    {
        $this->assertFalse($this->pragma->handlesToken($token));
    }

    /**
     * @dataProvider invalidTokens
     */
    public function testParseReturnsTokenUnchangedForTokensItDoesNotHandle($token)
    {
        $struct = [$token, 'foo'];
        $this->assertSame($struct, $this->pragma->parse($struct));
    }

    public function testParseLeavesTokenUnchangedIfItDoesNotHaveContextualInfo()
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo'
        ];
        $result = $this->pragma->parse($struct);
        $this->assertSame($struct, $result);
    }

    public function testParseReturnsSameTokenType()
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo|css'
        ];
        $result = $this->pragma->parse($struct);
        $this->assertNotSame($struct, $result);
        $this->assertInternalType('array', $result);
        $this->assertSame(Lexer::TOKEN_VARIABLE, $result[0]);
        return $result;
    }

    /**
     * @depends testParseReturnsSameTokenType
     */
    public function testParseStripsContextualInfoFromTokenData($token)
    {
        $this->assertEquals('foo', $token[1]);
        return $token;
    }

    /**
     * @depends testParseStripsContextualInfoFromTokenData
     */
    public function testParseAddsContextualDataAsThirdElementOfToken($token)
    {
        $this->assertEquals('css', $token[2]);
    }

    /**
     * @dataProvider invalidTokens
     */
    public function testRenderReturnsNullForTokensItDoesNotHandle($token)
    {
        $struct = [
            $token,
            'foo',
            'css',
        ];

        $this->assertNull($this->pragma->render($struct, ['foo' => 'value'], [], $this->mustache));
    }

    public function testRenderReturnsNullIfTokenHasNoContextualInfo()
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
        ];

        $this->assertNull($this->pragma->render($struct, ['foo' => 'value'], [], $this->mustache));
    }

    public function escapeContexts()
    {
        return [
            'html' => ['html', 'escapeHtml'],
            'attr' => ['attr', 'escapeHtmlAttr'],
            'js'   => ['js', 'escapeJs'],
            'css'  => ['css', 'escapeCss'],
            'url'  => ['url', 'escapeUrl'],
        ];
    }

    /**
     * @dataProvider escapeContexts
     */
    public function testRenderEscapesArrayViewUsingContextualInfo($context, $escapeMethod)
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
            $context
        ];
        $view = ['foo' => 'What\'s up, Doctor & Nurse?'];

        $expected = $this->escaper->{$escapeMethod}($view['foo']);

        $this->assertEquals($expected, $this->pragma->render($struct, $view, [], $this->mustache));
    }

    /**
     * @dataProvider escapeContexts
     */
    public function testRenderEscapesCallableArrayViewUsingContextualInfo($context, $escapeMethod)
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
            $context
        ];
        $view = ['foo' => function () {
            return 'What\'s up, Doctor & Nurse?';
        }];

        $expected = $this->escaper->{$escapeMethod}($view['foo']());

        $this->assertEquals($expected, $this->pragma->render($struct, $view, [], $this->mustache));
    }

    /**
     * @dataProvider escapeContexts
     */
    public function testRenderEscapesObjectViewUsingContextualInfo($context, $escapeMethod)
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
            $context
        ];
        $view = (object) ['foo' => 'What\'s up, Doctor & Nurse?'];

        $expected = $this->escaper->{$escapeMethod}($view->foo);

        $this->assertEquals($expected, $this->pragma->render($struct, $view, [], $this->mustache));
    }

    /**
     * @dataProvider escapeContexts
     */
    public function testRenderEscapesCallableObjectViewUsingContextualInfo($context, $escapeMethod)
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
            $context
        ];
        $view = (object) ['foo' => function () {
            return 'What\'s up, Doctor & Nurse?';
        }];

        $expected = $this->escaper->{$escapeMethod}(call_user_func($view->foo));

        $this->assertEquals($expected, $this->pragma->render($struct, $view, [], $this->mustache));
    }

    /**
     * @dataProvider escapeContexts
     */
    public function testRenderEscapesScalarViewUsingContextualInfo($context, $escapeMethod)
    {
        $struct = [
            Lexer::TOKEN_VARIABLE,
            'foo',
            $context
        ];
        $view = 'What\'s up, Doctor & Nurse?';

        $expected = $this->escaper->{$escapeMethod}($view);

        $this->assertEquals($expected, $this->pragma->render($struct, $view, [], $this->mustache));
    }
}
