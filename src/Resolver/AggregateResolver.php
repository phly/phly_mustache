<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Resolver;

use Countable;
use IteratorAggregate;
use Phly\Mustache\Exception;
use Zend\Stdlib\PriorityQueue;

class AggregateResolver implements Countable, IteratorAggregate, ResolverInterface
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
     * Does the aggregate contain a resolver of the specified type?
     *
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        foreach ($this as $resolver) {
            if ($resolver instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch one or more resolvers that match the given type.
     *
     * @param string $type
     * @return ResolverInterface Return the matched instance, or an aggregate
     *     containing all matched instances.
     * @throws Exception\ResolverTypeNotFoundException if no resolvers of the type are found.
     */
    public function fetchByType($type)
    {
        if (! $this->hasType($type)) {
            throw new Exception\ResolverTypeNotFoundException();
        }

        $resolvers = new self();

        foreach ($this as $resolver) {
            if ($resolver instanceof $type) {
                $resolvers->attach($resolver);
            }
        }

        if (1 === count($resolvers)) {
            return $resolvers->queue->extract();
        }

        return $resolvers;
    }
}
