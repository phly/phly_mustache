<?php
/**
 * phly_mustache
 *
 * @category   Phly
 * @package    phly_mustache
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
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
 * @category   Phly
 * @package    phly_mustache
 * @subpackage Resolver
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * Directory separator token in template names.
     * @var string
     */
    protected $separator = '/';

    /**
     * File suffix to use with templates.
     * @var string
     */
    protected $suffix = '.mustache';

    /**
     * Path on which to look for templates.
     * @var string
     */
    protected $templatePath;

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
     * Set value for templatePath
     *
     * @param  mixed templatePath
     * @return DefaultResolver
     * @throws Exception\InvalidTemplatePathException
     */
    public function setTemplatePath($templatePath)
    {
        if (!is_dir($templatePath)) {
            throw new Exception\InvalidTemplatePathException(sprintf(
                '%s expects a valid path to a directory; received "%s"',
                __METHOD__,
                $templatePath
            ));
        }

        $templatePath = rtrim((string) $templatePath, '/\\');
        $this->getTemplatePath()->push($templatePath);
        return $this;
    }

    /**
     * Get value for templatePath
     *
     * @return mixed
     */
    public function getTemplatePath()
    {
        if (!$this->templatePath instanceof SplStack) {
            $this->templatePath = new SplStack;
        }
        return $this->templatePath;
    }

    /**
     * Clear/initialize the template path stack
     *
     * @return void
     */
    public function clearTemplatePath()
    {
        $this->templatePath = new SplStack();
    }

    /**
     * Resolve a template to its file
     *
     * @param  string $template
     * @return false|string Returns false if unable to resolve the template to a path
     */
    public function resolve($template)
    {
        $segments     = explode($this->getSeparator(), $template);
        $relativePath = implode(DIRECTORY_SEPARATOR, $segments)
                      . $this->getSuffix();

        foreach ($this->getTemplatePath() as $path) {
            if (!empty($path)) {
                $path .= DIRECTORY_SEPARATOR;
            }

            $filename = $path . $relativePath;
            if (file_exists($filename)) {
                return file_get_contents($filename);
            }
        }

        return false;
    }
}
