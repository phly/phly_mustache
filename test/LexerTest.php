<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache;

use Phly\Mustache\Exception;
use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\PragmaCollection;
use Phly\Mustache\Pragma\PragmaInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;

/**
 * Integration tests for Lexer.
 */
class LexerTest extends TestCase
{
    public function setUp()
    {
        $this->pragmas  = new PragmaCollection();
        $this->mustache = $this->prophesize(Mustache::class);
        $this->mustache->getPragmas()->willReturn($this->pragmas);

        $this->lexer    = new Lexer();
    }

    public function testPragmaCanAlterTokenStruct()
    {
        $pragma = $this->prophesize(PragmaInterface::class);
        $pragma->getName()->willReturn('TEST');
        $pragma->handlesToken(Lexer::TOKEN_PRAGMA)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_CONTENT)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_VARIABLE)->willReturn(true);
        $pragma
            ->parse([
                Lexer::TOKEN_VARIABLE,
                'test|js',
            ])
            ->willReturn([
                Lexer::TOKEN_VARIABLE,
                'test',
                'js'
            ]);
        $this->pragmas->add($pragma->reveal());
        $tokens = $this->lexer->compile($this->mustache->reveal(), '{{%TEST}}{{test|js}}');
        $this->assertContains([
            Lexer::TOKEN_VARIABLE,
            'test',
            'js'
        ], $tokens);
    }

    public function invalidReturnTokens()
    {
        return [
            'null'        => [null],
            'true'        => [true],
            'false'       => [false],
            'zero'        => [0],
            'int'         => [1],
            'zero-float'  => [0.0],
            'float'       => [1.1],
            'string'      => ['string'],
            'empty-array' => [[]],
            'short-array' => [[Lexer::TOKEN_VARIABLE]],
            'object'      => [(object) ['token' => Lexer::TOKEN_VARIABLE, 'data' => 'data']],
        ];
    }

    /**
     * @dataProvider invalidReturnTokens
     */
    public function testPragmaReturningInvalidTokenStructRaisesException($returnToken)
    {
        $pragma = $this->prophesize(PragmaInterface::class);
        $pragma->getName()->willReturn('TEST');
        $pragma->handlesToken(Lexer::TOKEN_PRAGMA)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_CONTENT)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_VARIABLE)->willReturn(true);
        $pragma
            ->parse([
                Lexer::TOKEN_VARIABLE,
                'test|js',
            ])
            ->willReturn($returnToken);
        $this->pragmas->add($pragma->reveal());

        $this->setExpectedException('Phly\Mustache\Exception\InvalidTokenException');
        $this->lexer->compile($this->mustache->reveal(), '{{%TEST}}{{test|js}}');
    }

    public function testPragmaWillNotTriggerIfPragmaHasNotBeenDeclaredInCurrentScope()
    {
        $pragma = $this->prophesize(PragmaInterface::class);
        $pragma->getName()->willReturn('TEST');
        $pragma->handlesToken(Lexer::TOKEN_PRAGMA)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_CONTENT)->willReturn(false);
        $pragma->handlesToken(Lexer::TOKEN_VARIABLE)->willReturn(true);
        $pragma
            ->parse(Argument::any())
            ->shouldNotBeCalled();

        $this->pragmas->add($pragma->reveal());

        $tokens   = $this->lexer->compile($this->mustache->reveal(), '{{test|js}}');
        $found    = false;
        $expected = [ Lexer::TOKEN_VARIABLE, 'test|js'];
        foreach ($tokens as $struct) {
            if ($struct === $expected) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Expected token struct not received');
    }
}
