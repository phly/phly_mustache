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

use Phly\Mustache\Resolver\DefaultResolver;

/**
 * Unit tests for default resolver
 *
 * @category   Phly
 * @package    phly_mustache
 * @subpackage UnitTests
 */
class DefaultResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = new DefaultResolver();
        $this->resolver->setTemplatePath(__DIR__ . '/templates');
    }

    public function templateNames()
    {
        return array(
            array('foo'),
            array('foo/bar'),
            array('foo/bar/baz'),
        );
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingMustacheSuffixByDefault($template)
    {
        $expected = $template . '.mustache';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingProvidedSuffix($template)
    {
        $this->resolver->setSuffix('tpl');
        $expected = $template . '.tpl';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingSpecifiedDirectorySeparator($template)
    {
        $this->resolver->setSeparator('.');
        $expected = $template . '.mustache';
        $template = str_replace('/', '.', $template);
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testUsesPathStackInternally($template)
    {
        $this->resolver->clearTemplatePath();
        $this->resolver->setTemplatePath(__DIR__ . '/templates');
        $this->resolver->setTemplatePath(__DIR__);
        $expected = $template . '.mustache';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }
}
