<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Resolver;

use Phly\Mustache\Exception;
use SplStack;

/**
 * Default resolver implementation
 *
 * By default, assumes ".mustache" extension, and that a normal directory
 * separator ('/') is used. However, both are configurable.
 *
 * Additionally, allows segregating paths by namespace, and will resolve paths
 * in the format `namespace::template`. During resolution, paths registered
 * with the given namespace are queried first, and provided the template; if
 * none match, it attempts to match paths on the default namespace.
 *
 * If no namespace is provided, it only searches paths on the default
 * namespace (which is used if no namespace is provided when registering a
 * template path).
 */
class DefaultResolver implements ResolverInterface
{
    const DEFAULT_NAMESPACE = '__DEFAULT__';

    /**
     * @var SplStack[]
     */
    private $paths = [];

    /**
     * Directory separator token in template names.
     * @var string
     */
    private $separator = '/';

    /**
     * File suffix to use with templates.
     * @var string
     */
    private $suffix = '.mustache';

    /**
     * Set directory separator character
     *
     * @param  string $separator
     * @return DefaultResolver
     */
    public function setSeparator($separator)
    {
        $this->separator = (string) $separator;
        return $this;
    }

    /**
     * Get directory separator character
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Set file suffix
     *
     * @param  string $suffix
     * @return DefaultResolver
     */
    public function setSuffix($suffix)
    {
        $suffix = ltrim((string) $suffix, '.');
        $this->suffix = '.' . $suffix;
        return $this;
    }

    /**
     * Get template file suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Add a template path.
     *
     * Adds a template path to the internal stack. If a namespace is provided,
     * the path is added to the stack specific to that namespace; otherwise,
     * the default namespace is assumed.
     *
     * @param  string $templatePath
     * @param  null|string $namespace
     * @return DefaultResolver
     * @throws Exception\InvalidTemplatePathException
     */
    public function setTemplatePath($templatePath, $namespace = null)
    {
        if (! is_dir($templatePath)) {
            throw new Exception\InvalidTemplatePathException(sprintf(
                '%s expects a valid path to a directory; received "%s"',
                __METHOD__,
                $templatePath
            ));
        }

        if (null !== $namespace && ! is_string($namespace)) {
            throw new Exception\InvalidNamespaceException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;

        $templatePath = rtrim((string) $templatePath, '/\\');
        $this->getTemplatePath($namespace)->push($templatePath);

        return $this;
    }

    /**
     * Get value for templatePath
     *
     * @param null|string $namespace Defaults to DEFAULT_NAMESPACE when null
     * @return SplStack
     * @throws Exception\InvalidNamespaceException for invalid namespace values.
     */
    public function getTemplatePath($namespace = null)
    {
        if (null !== $namespace && ! is_string($namespace)) {
            throw new Exception\InvalidNamespaceException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;

        if (! array_key_exists($namespace, $this->paths)) {
            $this->paths[$namespace] = new SplStack;
        }

        return $this->paths[$namespace];
    }

    /**
     * Return a list of registered namespaces.
     *
     * Only returns those that have paths registered to them.
     *
     * @return string[]
     */
    public function getNamespaces()
    {
        $namespaces = [];

        foreach ($this->paths as $namespace => $paths) {
            if (count($paths)) {
                $namespaces[] = $namespace;
            }
        }

        return $namespaces;
    }

    /**
     * Resolve a template to its file
     *
     * @param  string $template
     * @return false|string Returns false if unable to resolve the template to a path
     */
    public function resolve($template)
    {
        $namespace = self::DEFAULT_NAMESPACE;
        if (preg_match('#^(?P<namespace>[^:]+)::(?P<template>.*)$#', $template, $matches)) {
            $namespace = $matches['namespace'];
            $template  = $matches['template'];
        }

        $segments = explode($this->getSeparator(), $template);
        $template = implode('/', $segments) . $this->getSuffix();

        $path = $this->fetchTemplateForNamespace($template, $namespace);

        if ($path !== false || $namespace === self::DEFAULT_NAMESPACE) {
            return $path;
        }

        return $this->fetchTemplateForNamespace($template, self::DEFAULT_NAMESPACE);
    }

    /**
     * Attempt to retrieve a template for a given namespace.
     *
     * @param string $template
     * @param string $namespace
     * @return false|string False on failure to resolve, string path otherwise.
     */
    private function fetchTemplateForNamespace($template, $namespace)
    {
        foreach ($this->getTemplatePath($namespace) as $path) {
            if (! empty($path)) {
                $path .= '/';
            }

            $filename = $path . $template;
            if (file_exists($filename)) {
                return file_get_contents($filename);
            }
        }

        return false;
    }
}
