<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Resolver;

use Phly\Mustache\Resolver\DefaultResolver;
use PHPUnit_Framework_TestCase as TestCase;
use Traversable;

/**
 * Unit tests for default resolver.
 */
class DefaultResolverTest extends TestCase
{
    public function setUp()
    {
        $this->templates      = __DIR__ . '/templates';
        $this->suiteTemplates = __DIR__ . '/../templates';
        $this->resolver       = new DefaultResolver();
    }

    public function assertContainsPath($path, Traversable $paths, $message = '')
    {
        $message = $message ?: 'Path not found in set';
        $found   = false;

        foreach ($paths as $test) {
            if ($test === $path) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, $message);
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
        $this->resolver->addTemplatePath($this->suiteTemplates);
        $expected = $template . '.mustache';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingProvidedSuffix($template)
    {
        $this->resolver->addTemplatePath($this->suiteTemplates);
        $this->resolver->setSuffix('tpl');
        $expected = $template . '.tpl';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    /**
     * @dataProvider templateNames
     */
    public function testResolvesUsingSpecifiedDirectorySeparator($template)
    {
        $this->resolver->addTemplatePath($this->suiteTemplates);
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
        $this->resolver->addTemplatePath($this->suiteTemplates);
        $this->resolver->addTemplatePath(__DIR__);
        $expected = $template . '.mustache';
        $this->assertContains($expected, $this->resolver->resolve($template));
    }

    public function testCanAddAndRetrievePathsWithNamespaces()
    {
        $this->resolver->addTemplatePath($this->templates . '/test', 'test');
        $paths = $this->assertContainsPath(
            $this->templates . '/test',
            $this->resolver->getTemplatePath('test')
        );
    }

    public function testAddingAndRetrievingPathWithoutNamespaceUsesDefaultNamespace()
    {
        $this->resolver->addTemplatePath($this->templates);
        $paths = $this->assertContainsPath(
            $this->templates,
            $this->resolver->getTemplatePath()
        );
    }

    public function testCanRetrieveRegisteredNamespaces()
    {
        $namespaces = ['test', 'namespace', 'default'];
        foreach ($namespaces as $namespace) {
            $this->resolver->addTemplatePath(sprintf(
                '%s/%s',
                $this->templates,
                $namespace
            ), $namespace);
        }

        $this->assertEquals($namespaces, $this->resolver->getNamespaces());
    }

    public function namespaces()
    {
        return [
            'test'      => ['test'],
            'default'   => ['default'],
            'namespace' => ['namespace'],
        ];
    }

    /**
     * @dataProvider namespaces
     */
    public function testCanResolveTemplatesByNamespace($namespace)
    {
        foreach ($this->namespaces() as $inject) {
            $ns = array_shift($inject);
            $this->resolver->addTemplatePath(sprintf(
                '%s/%s',
                $this->templates,
                $ns
            ), $ns);
        }

        $template = sprintf('%s::index', $namespace);
        $result   = $this->resolver->resolve($template);

        $expected = file_get_contents(sprintf('%s/%s/index.mustache', $this->templates, $namespace));
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider namespaces
     */
    public function testTemplateResolutionByNamespaceFallsBackToDefaultNamespaceWhenNecessary($namespace)
    {
        $this->resolver->addTemplatePath($this->templates);
        foreach ($this->namespaces() as $inject) {
            $ns = array_shift($inject);
            $this->resolver->addTemplatePath(sprintf(
                '%s/%s',
                $this->templates,
                $ns
            ), $ns);
        }

        $template = sprintf('%s::fallback', $namespace);
        $result   = $this->resolver->resolve($template);

        $expected = file_get_contents(sprintf('%s/fallback.mustache', $this->templates));
        $this->assertEquals($expected, $result);
    }

    public function testResolvingNonNamespacedTemplatesUsesPathsFromDefaultNamespace()
    {
        foreach ($this->namespaces() as $inject) {
            $ns = array_shift($inject);
            $this->resolver->addTemplatePath(sprintf(
                '%s/%s',
                $this->templates,
                $ns
            ), $ns);
        }
        $this->resolver->addTemplatePath($this->templates);

        $result   = $this->resolver->resolve('index');
        $expected = file_get_contents(sprintf('%s/index.mustache', $this->templates));
        $this->assertEquals($expected, $result);
    }
}
