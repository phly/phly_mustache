<?php
/**
 * phly_mustache
 *
 * @category   PhlyTest
 * @package    phly_mustache
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Unit tests for Mustache implementation
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class PhlyTest_Mustache_MustacheTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mustache = new Phly_Mustache_Mustache();
        $this->mustache->setTemplatePath(__DIR__ . '/templates');
    }

    public function testRendersStringTemplates()
    {
        $test = $this->mustache->render(
            'Hello {{planet}}',
            array('planet' => 'World')
        );
        $this->assertEquals('Hello World', $test);
    }

    public function testRendersFileTemplates()
    {
        $test = $this->mustache->render('renders-file-templates', array(
            'planet' => 'World',
        ));
        $this->assertEquals('Hello World', trim($test));
    }

    public function testCanUseObjectPropertiesForSubstitutions()
    {
        $view = new stdClass;
        $view->planet = 'World';
        $test = $this->mustache->render(
            'Hello {{planet}}',
            $view
        );
        $this->assertEquals('Hello World', $test);
    }

    public function testCanUseMethodReturnValueForSubstitutions()
    {
        $chris = new PhlyTest_Mustache_TestAsset_ViewWithMethod;
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
        $chris = new PhlyTest_Mustache_TestAsset_ViewWithMethod;
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
        $chris = new PhlyTest_Mustache_TestAsset_ViewWithMethod;
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
        $chris = new PhlyTest_Mustache_TestAsset_ViewWithMethod;
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithArrayEnumerable;
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithTraversableObject;
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithHigherOrderSection();
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
        $view = array(
            'a' => array(
                'title' => 'this is an object',
                'description' => 'one of its attributes is a list',
                'list' => array(
                    array('label' => 'listitem1'),
                    array('label' => 'listitem2'),
                ),
            ),
        );
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithNestedObjects;
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
        $view = array('repo' => array());
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithObjectForPartial();
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
        $view = new PhlyTest_Mustache_TestAsset_ViewWithObjectForPartial();
        $test = $this->mustache->render(
            'template-with-aliased-partial',
            $view,
            array('winnings' => 'partial-template')
        );
        $expected = 'Welcome, Joe! You just won $1000 (which is $600 after tax)';
        $this->assertEquals($expected, trim($test));
    }

    public function testEscapesStandardCharacters()
    {
        $view = array('foo' => 't&h\\e"s<e>');
        $test = $this->mustache->render(
            '{{foo}}',
            $view
        );
        $this->assertEquals('t&amp;h\\e&quot;s&lt;e&gt;', $test);
    }

    public function testTripleMustachesPreventEscaping()
    {
        $view = array('foo' => 't&h\\e"s<e>');
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
        $this->mustache->getRenderer()->addPragma(new Phly_Mustache_Pragma_ImplicitIterator());
        $view = array('foo' => array(1, 2, 3, 4, 5, 'french'));
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

    public function testAllowsSettingAlternateTemplateSuffix()
    {
        $this->mustache->setSuffix('html');
        $test = $this->mustache->render('alternate-suffix', array());
        $this->assertContains('alternate template suffix', $test);
    }

    public function testStripsCommentsFromRenderedOutput()
    {
        $test = $this->mustache->render('template-with-comments', array());
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
        $test = $this->mustache->render('template-with-delim-set', array('substitution' => 'working'));
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
        $test = $this->mustache->render('template-with-delim-set-in-section', array(
            'content' => 'style',
            'section' => array(
                'name' => '-World',
            ),
            'postcontent' => 'P.S. Done',
        ));
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
        $test = $this->mustache->render('template-with-sections-and-delim-set', array('content' => 'style', 'substitution' => array('name' => '-World')));
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
        $test = $this->mustache->render('template-with-partials-and-delim-set', array(
            'substitution' => 'style',
            'value'        => 1000000,
            'taxed_value'  =>  400000,
        ));
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
        $this->mustache->getRenderer()->addPragma(new Phly_Mustache_Pragma_ImplicitIterator());
        $test = $this->mustache->render('template-with-pragma-in-section', array(
            'type' => 'style',
            'section' => array(
                'subsection' => array(1, 2, 3),
            ),
            'section2' => array(
                'subsection' => array(1, 2, 3),
            ),
        ));
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
        $this->mustache->getRenderer()->addPragma(new Phly_Mustache_Pragma_ImplicitIterator());
        $test = $this->mustache->render('template-with-pragma-and-partial', array(
            'type' => 'style',
            'section' => array(
                'subsection' => array(1, 2, 3),
            ),
        ));
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
        foreach(range(1, 6) as $content) {
            $this->assertEquals(1, substr_count($test, $content));
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
        return array(
            'top_nodes' => array(
                'contents' => '1',
                'children' => array(
                    array(
                        'contents' => '2',
                        'children' => array(
                            array(
                                'contents' => 3,
                                'children' => array(),
                            )
                        ),
                    ),
                    array(
                        'contents' => '4',
                        'children' => array(
                            array(
                                'contents' => '5',
                                'children' => array(
                                    array(
                                        'contents' => '6',
                                        'children' => array(),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
