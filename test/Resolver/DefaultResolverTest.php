<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Resolver;

use Phly\Mustache\Resolver\DefaultResolver;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Unit tests for default resolver.
 */
class DefaultResolverTest extends TestCase
{
    public function setUp()
    {
        $this->resolver = new DefaultResolver();
        $this->resolver->setTemplatePath(__DIR__ . '/../templates');
    }

    public function templateNames()
    {
        return [
            ['foo'],
            ['foo/bar'],
            ['foo/bar/baz'],
        ];
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
        $this->resolver->setTemplatePath(__DIR__ . '/../templates');
        $this->resolver->setTemplatePath(__DIR__);
        $expected = $template . '.mustache';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }
}
