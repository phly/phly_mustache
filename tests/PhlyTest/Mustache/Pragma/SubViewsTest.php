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
 * Unit tests for Sub-Views pragma
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class PhlyTest_Mustache_Pragma_SubViewsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mustache = new Phly_Mustache_Mustache();
        $this->mustache->setTemplatePath(__DIR__ . '/../templates');
        $subViews = new Phly_Mustache_Pragma_SubViews();
        $subViews->setManager($this->mustache);
        $this->mustache->getRenderer()->addPragma($subViews);
    }

    public function testSubViewContentIsCapturedInParent()
    {
        $content = new Phly_Mustache_Pragma_SubView('sub-view-template', array(
            'greeting' => 'Hello',
            'name'     => 'World',
        ));
        $view = array('content' => $content);
        $test = $this->mustache->render('template-with-sub-view', $view);
        $this->assertRegexp('/Header content.*?Hello, World.*Footer content/s', $test);
    }

    public function testRendersNestedSubViews()
    {
        $sidebar = new Phly_Mustache_Pragma_SubView('sub-view-sidebar', array('name' => 'final'));
        $content = new Phly_Mustache_Pragma_SubView('sub-view-template', array(
            'greeting' => 'Goodbye',
            'name'     => 'cruel world',
        ));
        $mainContent = new Phly_Mustache_Pragma_SubView('sub-view-containing-sub-views', array(
            'content' => $content,
            'sidebar' => $sidebar,
        ));
        $view = array('content' => $mainContent);
        $test = $this->mustache->render('template-with-sub-view', $view);
        $this->assertRegexp('/Header content.*?Goodbye, cruel world.*?break.*?final sidebar.*?Footer content/s', $test);
    }

    public function testSubViewUsesParentViewWhenNoViewProvided()
    {
        $sidebar = new Phly_Mustache_Pragma_SubView('sub-view-sidebar');
        $content = new Phly_Mustache_Pragma_SubView('sub-view-template');
        $view = array(
            'name'     => 'bat', 
            'greeting' => 'Shabaz', 
            'content'  => $content, 
            'sidebar'  => $sidebar,
        );
        $test = $this->mustache->render('sub-view-containing-sub-views', $view);
        $this->assertRegexp('/Shabaz, bat.*?bat sidebar/s', $test, $test);
    }
}
