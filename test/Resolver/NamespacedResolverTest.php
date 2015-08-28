<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Resolver;

use Phly\Mustache\Resolver\NamespacedResolver;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use ReflectionProperty;
use Traversable;

/**
 * Unit tests for aggregate resolver.
 */
class NamespacedResolverTest extends TestCase
{
    public function setUp()
    {
        $this->templates = __DIR__ . '/templates';
        $this->resolver = new NamespacedResolver();
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

    public function testCanAddAndRetrievePathsWithNamespaces()
    {
        $this->resolver->setTemplatePath($this->templates . '/test', 'test');
        $paths = $this->assertContainsPath(
            $this->templates . '/test',
            $this->resolver->getTemplatePath('test')
        );
    }

    public function testAddingAndRetrievingPathWithoutNamespaceUsesDefaultNamespace()
    {
        $this->resolver->setTemplatePath($this->templates);
        $paths = $this->assertContainsPath(
            $this->templates,
            $this->resolver->getTemplatePath()
        );
    }

    public function testCanRetrieveRegisteredNamespaces()
    {
        $namespaces = ['test', 'namespace', 'default'];
        foreach ($namespaces as $namespace) {
            $this->resolver->setTemplatePath(sprintf(
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
            $this->resolver->setTemplatePath(sprintf(
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
        $this->resolver->setTemplatePath($this->templates);
        foreach ($this->namespaces() as $inject) {
            $ns = array_shift($inject);
            $this->resolver->setTemplatePath(sprintf(
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
            $this->resolver->setTemplatePath(sprintf(
                '%s/%s',
                $this->templates,
                $ns
            ), $ns);
        }
        $this->resolver->setTemplatePath($this->templates);

        $result   = $this->resolver->resolve('index');
        $expected = file_get_contents(sprintf('%s/index.mustache', $this->templates));
        $this->assertEquals($expected, $result);
    }
}
