<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Resolver;

use Countable;
use IteratorAggregate;
use Zend\Stdlib\PriorityQueue;

class AggregateMustacheResolver implements Countable, IteratorAggregate, ResolverInterface
{
    /**
     * @var PriorityQueue
     */
    private $queue;

    /**
     * Constructor.
     *
     * Creates the internal priority queue.
     */
    public function __construct()
    {
        $this->queue = new PriorityQueue();
    }

    /**
     * Return count of attached resolvers
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * IteratorAggregate: return internal iterator.
     *
     * @return PriorityQueue
     */
    public function getIterator()
    {
        return $this->queue;
    }

    /**
     * Attach a resolver
     *
     * @param  ResolverInterface $resolver
     * @param  int $priority
     * @return self
     */
    public function attach(ResolverInterface $resolver, $priority = 1)
    {
        $this->queue->insert($resolver, $priority);
        return $this;
    }

    /**
     * Resolve a template name to a resource the renderer can consume.
     *
     * @param  string $template
     * @return false|string
     */
    public function resolve($template)
    {
        foreach ($this->queue as $resolver) {
            $resource = $resolver->resolve($template);
            if (false !== $resource) {
                return $resource;
            }
        }

        return false;
    }
}
