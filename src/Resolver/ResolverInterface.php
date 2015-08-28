<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Resolver;

/**
 * Base resolver interface
 */
interface ResolverInterface
{
    /**
     * Resolve a template name
     *
     * Resolve a template name to mustache content or a set of tokens.
     *
     * @param  string $template
     * @return string|array
     */
    public function resolve($template);
}
