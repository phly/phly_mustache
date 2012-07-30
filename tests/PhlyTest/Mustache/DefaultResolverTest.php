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
    }

    public function templateNames()
    {
        return array(
            array('foo'),
            array('bar/baz'),
            array('bar/baz/bat'),
            array('../foo'),
        );
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingMustacheSuffixByDefault($template)
    {
        $expected = $template . '.mustache';
        $this->assertEquals($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingProvidedSuffix($template)
    {
        $this->resolver->setSuffix('tpl');
        $expected = $template . '.tpl';
        $this->assertEquals($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingProvidedTemplatePath($template)
    {
        $this->resolver->setTemplatePath(__DIR__ . '/templates');
        $expected = __DIR__ . '/templates/' . $template . '.mustache';
        $this->assertEquals($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingSpecifiedDirectorySeparator($template)
    {
        $this->resolver->setSeparator('.');
        if (strstr($template, '.')) {
            // not testing this
            return;
        }
        $expected = $template . '.mustache';
        $template = str_replace('/', '.', $template);
        $this->assertEquals($expected, $this->resolver->resolve($template));
    }
}
