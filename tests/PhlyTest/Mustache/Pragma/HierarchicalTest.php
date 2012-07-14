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

namespace PhlyTest\Mustache\Pragma;

use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\Hierarchical;
use stdClass;

/**
 * Unit tests for Hierarchical pragma
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class HierarchicalTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mustache = new Mustache();
        $this->mustache->setTemplatePath(__DIR__ . '/../templates');
        $hierarchical = new Hierarchical();
        $hierarchical->setManager($this->mustache);
        $this->mustache->getRenderer()->addPragma($hierarchical);
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
}
