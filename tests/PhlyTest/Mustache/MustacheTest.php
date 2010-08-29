<?php

namespace PhlyTest\Mustache;

use Phly\Mustache\Mustache;

class MustacheTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mustache = new Mustache();
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
        $this->markTestIncomplete('Still determining how to handle higher order sections');
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

    public function testAllowsAlteringBehaviorUsingPragmas()
    {
        $this->markTestIncomplete('Looking for examples of use cases');
    }

    public function testHonorsImplicitIteratorPragma()
    {
        $this->markTestIncomplete('Pragmas not yet implemented');
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
}
