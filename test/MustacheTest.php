<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache;

use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma;
use Phly\Mustache\Pragma\ImplicitIterator;
use Phly\Mustache\Resolver\AggregateResolver;
use Phly\Mustache\Resolver\DefaultResolver;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

/**
 * Unit tests for Mustache implementation
 */
class MustacheTest extends TestCase
{
    public function setUp()
    {
        $resolver = new DefaultResolver();
        $resolver->addTemplatePath(__DIR__ . '/templates');

        $this->mustache = new Mustache();
        $this->mustache->setResolver($resolver);
    }

    public function testRendersStringTemplates()
    {
        $test = $this->mustache->render(
            'Hello {{planet}}',
            ['planet' => 'World']
        );
        $this->assertEquals('Hello World', $test);
    }

    public function testRendersFileTemplates()
    {
        $test = $this->mustache->render('renders-file-templates', [
            'planet' => 'World',
        ]);
        $this->assertEquals('Hello World', trim($test));
    }

    public function testCanUseObjectPropertiesForSubstitutions()
    {
        $view = new \stdClass;
        $view->planet = 'World';
        $test = $this->mustache->render(
            'Hello {{planet}}',
            $view
        );
        $this->assertEquals('Hello World', $test);
    }

    public function testCanUseMethodReturnValueForSubstitutions()
    {
        $chris = new TestAsset\ViewWithMethod;
        $test = $this->mustache->render(
            'template-with-method-substitution',
            $chris
        );
        $expected =<<<EOT
Hello Chris
You have just won \$600000!
EOT;
        $this->assertEquals($expected, trim($test));
    }

    public function testTemplateMayUseConditionals()
    {
        $chris = new TestAsset\ViewWithMethod;
        $test = $this->mustache->render(
            'template-with-conditional',
            $chris
        );
        $expected =<<<EOT
Hello Chris
You have just won \$1000000!
Well, \$600000, after taxes.

EOT;
        $this->assertEquals($expected, $test);
    }

    public function testConditionalIsSkippedIfValueIsFalse()
    {
        $chris = new TestAsset\ViewWithMethod;
        $chris->in_ca = false;
        $test = $this->mustache->render(
            'template-with-conditional',
            $chris
        );
        $expected =<<<EOT
Hello Chris
You have just won \$1000000!

EOT;
        $this->assertEquals($expected, $test);
    }

    public function testConditionalIsSkippedIfValueIsEmpty()
    {
        $chris = new TestAsset\ViewWithMethod;
        $chris->in_ca = null;
        $test = $this->mustache->render(
            'template-with-conditional',
            $chris
        );
        $expected =<<<EOT
Hello Chris
You have just won \$1000000!

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group iteration
     */
    public function testTemplateIteratesArrays()
    {
        $view = new TestAsset\ViewWithArrayEnumerable;
        $test = $this->mustache->render(
            'template-with-enumerable',
            $view
        );
        $expected =<<<EOT
Joe's shopping card:
<ul>
    <li>bananas</li>
    <li>apples</li>
</ul>

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group iteration
     */
    public function testTemplateIteratesTraversableObjects()
    {
        $view = new TestAsset\ViewWithTraversableObject;
        $test = $this->mustache->render(
            'template-with-enumerable',
            $view
        );
        $expected =<<<EOT
Joe's shopping card:
<ul>
    <li>bananas</li>
    <li>apples</li>
</ul>

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group higher-order
     */
    public function testHigherOrderSectionsRenderInsideOut()
    {
        $view = new TestAsset\ViewWithHigherOrderSection();
        $test = $this->mustache->render(
            '{{#bolder}}Hi {{name}}.{{/bolder}}',
            $view
        );
        $expected =<<<EOT
<b>Hi Tater.</b>
EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group dereference
     * @group whitespace-issues
     */
    public function testTemplateWillDereferenceNestedArrays()
    {
        $view = [
            'a' => [
                'title' => 'this is an object',
                'description' => 'one of its attributes is a list',
                'list' => [
                    ['label' => 'listitem1'],
                    ['label' => 'listitem2'],
                ],
            ],
        ];
        $test = $this->mustache->render(
            'template-with-dereferencing',
            $view
        );
        $expected =<<<EOT
    <h1>this is an object</h1>
    <p>one of its attributes is a list</p>
    <ul>
        <li>listitem1</li>
                <li>listitem2</li>
            </ul>

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group dereference
     * @group whitespace-issues
     */
    public function testTemplateWillDereferenceNestedObjects()
    {
        $view = new TestAsset\ViewWithNestedObjects;
        $test = $this->mustache->render(
            'template-with-dereferencing',
            $view
        );
        $expected =<<<EOT
    <h1>this is an object</h1>
    <p>one of its attributes is a list</p>
    <ul>
        <li>listitem1</li>
                <li>listitem2</li>
            </ul>

EOT;
        $this->assertEquals($expected, $test);
    }

    public function testInvertedSectionsRenderOnEmptyValues()
    {
        $view = ['repo' => []];
        $test = $this->mustache->render(
            'template-with-inverted-section',
            $view
        );
        $expected = 'No repos';
        $this->assertEquals($expected, trim($test));
    }

    /**
     * @group partial
     */
    public function testRendersPartials()
    {
        $view = new TestAsset\ViewWithObjectForPartial();
        $test = $this->mustache->render(
            'template-with-partial',
            $view
        );
        $expected = 'Welcome, Joe! You just won $1000 (which is $600 after tax)';
        $this->assertEquals($expected, trim($test));
    }

    /**
     * @group partial
     */
    public function testAllowsAliasingPartials()
    {
        $view = new TestAsset\ViewWithObjectForPartial();
        $test = $this->mustache->render(
            'template-with-aliased-partial',
            $view,
            ['winnings' => 'partial-template']
        );
        $expected = 'Welcome, Joe! You just won $1000 (which is $600 after tax)';
        $this->assertEquals($expected, trim($test));
    }

    public function testEscapesStandardCharacters()
    {
        $view = ['foo' => 't&h\\e"s<e>'];
        $test = $this->mustache->render(
            '{{foo}}',
            $view
        );
        $this->assertEquals('t&amp;h\\e&quot;s&lt;e&gt;', $test);
    }

    public function testTripleMustachesPreventEscaping()
    {
        $view = ['foo' => 't&h\\e"s<e>'];
        $test = $this->mustache->render(
            '{{{foo}}}',
            $view
        );
        $this->assertEquals('t&h\\e"s<e>', $test);
    }

    /**
     * @group pragma
     */
    public function testAllowsAlteringBehaviorUsingPragmas()
    {
        $this->markTestIncomplete('Looking for examples of use cases');
    }

    /**
     * @group pragma
     */
    public function testHonorsImplicitIteratorPragma()
    {
        $this->mustache->getRenderer()->addPragma(new Pragma\ImplicitIterator());
        $view = ['foo' => [1, 2, 3, 4, 5, 'french']];
        $test = $this->mustache->render(
            'template-with-implicit-iterator',
            $view
        );
        $expected =<<<EOT

    1
    2
    3
    4
    5
    french

EOT;
        $this->assertEquals($expected, $test);
    }

    public function testStripsCommentsFromRenderedOutput()
    {
        $test = $this->mustache->render('template-with-comments', []);
        $expected =<<<EOT
First line
Second line

Third line

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group delim
     */
    public function testAllowsSpecifyingAlternateDelimiters()
    {
        $test = $this->mustache->render('template-with-delim-set', ['substitution' => 'working']);
        $expected = <<<EOT
This is content, working, from new delimiters.

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group delim
     */
    public function testAlternateDelimitersSetInSectionOnlyApplyToThatSection()
    {
        $test = $this->mustache->render('template-with-delim-set-in-section', [
            'content' => 'style',
            'section' => [
                'name' => '-World',
            ],
            'postcontent' => 'P.S. Done',
        ]);
        $expected =<<<EOT
Some text with style
    -World
P.S. Done

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group delim
     */
    public function testAlternateDelimitersApplyToChildSections()
    {
        $test = $this->mustache->render(
            'template-with-sections-and-delim-set',
            ['content' => 'style', 'substitution' => ['name' => '-World']]
        );
        $expected = <<<EOT
Some text with style
    -World

EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group delim
     */
    public function testAlternateDelimitersDoNotCarryToPartials()
    {
        $test = $this->mustache->render('template-with-partials-and-delim-set', [
            'substitution' => 'style',
            'value'        => 1000000,
            'taxed_value'  =>  400000,
        ]);
        $expected =<<<EOT
This is content, style, from new delimiters.
You just won $1000000 (which is $400000 after tax)


EOT;
        $this->assertEquals($expected, $test);
    }

    /**
     * @group pragma
     */
    public function testPragmasAreSectionSpecific()
    {
        $this->mustache->getRenderer()->addPragma(new Pragma\ImplicitIterator());
        $test = $this->mustache->render('template-with-pragma-in-section', [
            'type' => 'style',
            'section' => [
                'subsection' => [1, 2, 3],
            ],
            'section2' => [
                'subsection' => [1, 2, 3],
            ],
        ]);
        $this->assertEquals(1, substr_count($test, '1'), $test);
        $this->assertEquals(1, substr_count($test, '2'), $test);
        $this->assertEquals(1, substr_count($test, '3'), $test);
    }

    /**
     * @group pragma
     * @group partial
     */
    public function testPragmasDoNotExtendToPartials()
    {
        $this->mustache->getRenderer()->addPragma(new Pragma\ImplicitIterator());
        $test = $this->mustache->render('template-with-pragma-and-partial', [
            'type' => 'style',
            'section' => [
                'subsection' => [1, 2, 3],
            ],
        ]);
        $this->assertEquals(1, substr_count($test, 'Some content, with style'));
        $this->assertEquals(1, substr_count($test, 'This is from the partial'));
        $this->assertEquals(0, substr_count($test, '1'));
        $this->assertEquals(0, substr_count($test, '2'));
        $this->assertEquals(0, substr_count($test, '3'));
    }

    /**
     * @group partial
     */
    public function testHandlesRecursivePartials()
    {
        $view = $this->getRecursiveView();
        $test = $this->mustache->render('crazy_recursive', $view);
        foreach (range(1, 6) as $content) {
            $this->assertEquals(1, substr_count($test, $content), 'Content: ' . $test);
        }
    }

    /**
     * @group whitespace-issues
     */
    public function testLexerStripsUnwantedWhitespaceFromTokens()
    {
        $view = $this->getRecursiveView();
        $test = $this->mustache->render('crazy_recursive', $view);
        $expected = <<<EOT
<html>
<body>
<ul>
        <li>
    1
    <ul>
            <li>
    2
    <ul>
            <li>
    3
    <ul>
    </ul>
</li>

            </ul>
</li>

                    <li>
    4
    <ul>
            <li>
    5
    <ul>
            <li>
    6
    <ul>
    </ul>
</li>

            </ul>
</li>

            </ul>
</li>

            </ul>
</li>

    </ul>
</body>
</html>

EOT;
        $this->assertEquals($expected, $test);
    }

    protected function getRecursiveView()
    {
        return [
            'top_nodes' => [
                'contents' => '1',
                'children' => [
                    [
                        'contents' => '2',
                        'children' => [
                            [
                                'contents' => 3,
                                'children' => [],
                            ]
                        ],
                    ],
                    [
                        'contents' => '4',
                        'children' => [
                            [
                                'contents' => '5',
                                'children' => [
                                    [
                                        'contents' => '6',
                                        'children' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @group injection-issues
     */
    public function testArrayValuesThatReferToPHPBuiltInsShouldNotCallThem()
    {
        $test = $this->mustache->render('template-referencing-php-function', [
            'message' => 'time',
        ]);
        $this->assertEquals('time', trim($test));
    }

    /**
     * @group injection-issues
     */
    public function testObjectPropertiesThatReferToPHPBuiltInsShouldNotCallThem()
    {
        $model = (object) ['message' => 'time'];
        $test  = $this->mustache->render('template-referencing-php-function', $model);
        $this->assertEquals('time', trim($test));
    }

    /**
     * @group injection-issues
     */
    public function testArrayValuesThatReferToStaticMethodsShouldNotCallThem()
    {
        $model = ['message' => 'DateTime::createFromFormat'];
        $test = $this->mustache->render('template-referencing-php-function', $model);
        $this->assertEquals('DateTime::createFromFormat', trim($test));
    }

    /**
     * @group injection-issues
     */
    public function testStringValuesThatReferToFunctionsShouldNotCallThem()
    {
        $model = ['message' => 'time'];
        $this->mustache->getRenderer()->addPragma(new ImplicitIterator());
        $test = $this->mustache->render('template-referencing-static-function-notempty', $model);
        $this->assertEquals('time', trim($test));
    }

    /**
     * @group injection-issues
     */
    public function testArrayValuesThatReferToStaticMethodsInArraySyntaxShouldNotCallThem()
    {
        $model = ['section' => ['DateTime', 'createFromFormat']];
        $this->mustache->getRenderer()->addPragma(new ImplicitIterator());
        $test = $this->mustache->render('template-referencing-static-function', $model);
        $this->assertEquals("DateTime\ncreateFromFormat", trim($test));
    }

    /**
     * @group issue-5
     */
    public function testStdClassAsViewShouldNotRaiseError()
    {
        $view = new stdClass;
        $view->content = 'This is the content';
        $test = $this->mustache->render('issue-5', $view);
        $this->assertEquals('This is the content', trim($test));
    }

    /**
     * @group issue-8
     */
    public function testDotNotationIsExandedToSubPropertyOfView()
    {
        $view = [
            'foo' => [
                'bar' => 'baz',
            ],
        ];
        $test = $this->mustache->render('dot-notation', $view);
        $this->assertEquals('baz', trim($test));
    }

    /**
     * @group issue-8
     */
    public function testWithDotNotationIfSubpropertyDoesNotExistEmptyStringIsRendered()
    {
        $view = [
            'foo' => 'bar',
        ];
        $test = $this->mustache->render('dot-notation', $view);
        $this->assertEquals('', trim($test));
    }

    public function testComposesAggregateResolverWithDefaultResolverComposedByDefault()
    {
        $mustache = new Mustache();
        $resolver = $mustache->getResolver();

        $this->assertInstanceOf(AggregateResolver::class, $resolver);
        $this->assertTrue($resolver->hasType(DefaultResolver::class));
    }
}
