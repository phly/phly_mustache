<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Pragma;

use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\SubView;
use Phly\Mustache\Pragma\SubViews;
use Phly\Mustache\Resolver\DefaultResolver;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

/**
 * Unit tests for Sub-Views pragma
 */
class SubViewsTest extends TestCase
{
    public function setUp()
    {
        $resolver = new DefaultResolver();
        $resolver->addTemplatePath(__DIR__ . '/../templates');

        $this->mustache = new Mustache();
        $this->mustache->setResolver($resolver);

        $subViews = new SubViews();
        $subViews->setManager($this->mustache);
        $this->mustache->getRenderer()->addPragma($subViews);
    }

    public function testSubViewContentIsCapturedInParent()
    {
        $content = new SubView('sub-view-template', [
            'greeting' => 'Hello',
            'name'     => 'World',
        ]);
        $view = ['content' => $content];
        $test = $this->mustache->render('template-with-sub-view', $view);
        $this->assertRegexp('/Header content.*?Hello, World.*Footer content/s', $test);
    }

    public function testRendersNestedSubViews()
    {
        $sidebar = new SubView('sub-view-sidebar', ['name' => 'final']);
        $content = new SubView('sub-view-template', [
            'greeting' => 'Goodbye',
            'name'     => 'cruel world',
        ]);
        $mainContent = new SubView('sub-view-containing-sub-views', [
            'content' => $content,
            'sidebar' => $sidebar,
        ]);
        $view = ['content' => $mainContent];
        $test = $this->mustache->render('template-with-sub-view', $view);
        $this->assertRegexp('/Header content.*?Goodbye, cruel world.*?break.*?final sidebar.*?Footer content/s', $test);
    }

    public function testSubViewUsesParentViewWhenNoViewProvided()
    {
        $sidebar = new SubView('sub-view-sidebar');
        $content = new SubView('sub-view-template');
        $view = [
            'name'     => 'bat',
            'greeting' => 'Shabaz',
            'content'  => $content,
            'sidebar'  => $sidebar,
        ];
        $test = $this->mustache->render('sub-view-containing-sub-views', $view);
        $this->assertRegexp('/Shabaz, bat.*?bat sidebar/s', $test, $test);
    }

    /**
     * @group closure
     */
    public function testShouldRenderSubViewReturnedByClosure()
    {
        $view = ['closure' => function () {
            return new SubView('sub-view-template', [
                'greeting' => 'Shalom',
                'name'     => 'Ishmael',
            ]);
        }];
        $test = $this->mustache->render('sub-view-from-closure', $view);
        $this->assertContains('Shalom, Ishmael', $test);
    }

    /**
     * @group issue-5
     */
    public function testStdClassComposingSubViewShouldNotRaiseError()
    {
        $view    = new stdClass;
        $content = new SubView('sub-view-template', [
            'greeting' => 'Hello',
            'name'     => 'World',
        ]);
        $view->content = $content;
        $test = $this->mustache->render('issue-5-subview', $view);
        $this->assertEquals('Hello, World!', trim($test));
    }
}
