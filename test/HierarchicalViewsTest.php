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

namespace PhlyTest\Mustache;

use Phly\Mustache\Mustache;
use stdClass;

/**
 * Unit tests for hierarchical views
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class HierarchicalViewsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mustache = new Mustache();
        $this->mustache->setTemplatePath(__DIR__ . '/templates');
    }

    /**
     * @group issue-6
     */
    public function testUnderstandsHierarchicalTemplates()
    {
        $view = new stdClass;
        $view->username = 'Matthew';
        $test = $this->mustache->render('sub', $view);
        $this->assertContains('<title>Profile of Matthew | Twitter</title>', $test);
        $this->assertRegexp('/div class="content">\s+Here is Matthew\'s profile page\s+<\/div>/s', $test);
        $this->assertNotContains('Default title', $test);
        $this->assertNotContains('Default content of the page', $test);
    }

    /**
     * @group issue-6
     */
    public function testPlaceholdersAreRenderedAsUnnamedSections()
    {
        $view = new stdClass;
        $test = $this->mustache->render('super', $view);
        $this->assertContains('<title>Default title</title>', $test);
        $this->assertRegexp('/div class="content">\s+Default content of the page\s*<\/div>/s', $test);
    }

    /**
     * @group issue-6
     */
    public function testOnlyPlaceholdersWithReplacementsReceiveSubstitutions()
    {
        $view = new stdClass;
        $view->username = 'Matthew';
        $test = $this->mustache->render('sub-incomplete', $view);
        $this->assertContains('<title>Default title</title>', $test);
        $this->assertRegexp('/div class="content">\s+Here is Matthew\'s profile page\s+<\/div>/s', $test);
        $this->assertNotContains('Default content of the page', $test);
    }

    /**
     * @group issue-17
     */
    public function testCanRenderMultiplePlaceholders()
    {
        $test = $this->mustache->render('issue-17-child', array());
        $this->assertContains('<div class="span4">This is the sidebar content</div>', $test);
        $this->assertRegexp('#<div class="span8">\s+This is the primary content\s+</div>#s', $test);
    }

    /**
     * @group issue-17
     */
    public function testCanRenderNestedChildPlaceholders()
    {
        $test = $this->mustache->render('issue-17-nested-child', array());
        $this->assertContains('<div class="span4">This is the sidebar content</div>', $test);
        $this->assertRegexp('#<div class="span8">\s+This is the nested content\s+</div>#s', $test);
    }

    /**
     * @group issue-17
     */
    public function testNestedChildrenCanRenderPlaceholdersDefinedInParentChild()
    {
        $test = $this->mustache->render('issue-17-nested-child-2', array());
        $this->assertRegexp('#<div class="container-fluid">\s+<div class="row-fluid">.*?<div class="span9">\s+new content#s', $test, $test);
    }

    /**
     * @group issue-25
     */
    public function testSubLayoutsCanAlterContentOfParent()
    {
        $mustache = new Mustache();
        $mustache->setTemplatePath(__DIR__ . '/templates/no-layout-dups');

        $view = new stdClass;
        $view->name = 'Stan';

        $layout    = $mustache->render('layout', $view);
        $this->assertContains('Hello Stan', $layout);
        $this->assertContains('Default content of the page', $layout);

        $subLayout = $mustache->render('sub-layout', $view);
        $this->assertContains('Salutations Stan', $subLayout);
        $this->assertContains('Lorem ipsum, yada yada yada...', $subLayout);
    }

    /**
     * @group issue-25
     */
    public function testCanRenderCorrectTemplatesWhenExistingParentHasBeenRendered()
    {
        // set up existing cache
        $this->mustache->render('issue-25-child1', array());

        $test = $this->mustache->render('issue-25-child2', array());
        $this->assertContains('<div class="span4">This is the sidebar content for child2</div>', $test);
    }
}
