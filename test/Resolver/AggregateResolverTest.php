<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Resolver;

use Phly\Mustache\Resolver\AggregateResolver;
use Phly\Mustache\Resolver\DefaultResolver;
use Phly\Mustache\Resolver\ResolverInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use ReflectionProperty;

/**
 * Unit tests for aggregate resolver.
 */
class AggregateResolverTest extends TestCase
{
    public function setUp()
    {
        $this->resolver = new AggregateResolver();
    }

    public function assertQueueContains(ResolverInterface $resolver, AggregateResolver $aggregate, $message = '')
    {
        $message = $message ?: 'Failed to assert aggregate contains specified resolver';
        $found   = false;

        foreach ($aggregate as $test) {
            if ($test === $resolver) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, $message);
    }

    public function testCanAttachResolvers()
    {
        $resolver = $this->prophesize(ResolverInterface::class)->reveal();
        $this->resolver->attach($resolver);
        $this->assertQueueContains($resolver, $this->resolver);
    }

    public function testAggregateCountChangesAsResolversAreAttached()
    {
        $count = 0;

        for ($i = 0; $i < 5; $i += 1) {
            $this->assertCount($i, $this->resolver);

            $resolver = $this->prophesize(ResolverInterface::class)->reveal();
            $this->resolver->attach($resolver);
        }

        $this->assertCount($i, $this->resolver);
    }

    public function testCanIterateAggregate()
    {
        $resolvers = [];
        for ($i = 0; $i < 5; $i += 1) {
            $resolver = $this->prophesize(ResolverInterface::class)->reveal();
            $this->resolver->attach($resolver);
            $resolvers[$i] = $resolver;
        }

        $index = 0;
        foreach ($this->resolver as $resolver) {
            $this->assertSame($resolvers[$index], $resolver, sprintf('Failed to match index %d', $index));
            $index += 1;
        }
    }

    public function testIterationIsInPriorityOrder()
    {
        $resolvers = [];
        for ($i = 0; $i < 5; $i += 1) {
            $resolver = $this->prophesize(ResolverInterface::class)->reveal();
            $this->resolver->attach($resolver, $i);
            $resolvers[] = $resolver;
        }

        $indices = array_keys($resolvers);
        foreach ($this->resolver as $resolver) {
            $index = array_pop($indices);
            $this->assertSame($resolvers[$index], $resolver);
        }
    }

    public function testResolvingExecutesResolversUntilFirstReturnsNonFalseValue()
    {
        $first  = $this->prophesize(ResolverInterface::class);
        $second = $this->prophesize(ResolverInterface::class);
        $third  = $this->prophesize(ResolverInterface::class);

        $first->resolve(Argument::type('string'))->willReturn('resolved');
        $second->resolve(Argument::type('string'))->willReturn(false);
        $third->resolve(Argument::type('string'))->shouldNotBeCalled();

        $this->resolver->attach($first->reveal());
        $this->resolver->attach($second->reveal(), 100);
        $this->resolver->attach($third->reveal());

        $result = $this->resolver->resolve('template');
        $this->assertEquals('resolved', $result);
    }

    public function testCanTestIfResolverOfTypeIsPresent()
    {
        $this->assertFalse($this->resolver->hasType(DefaultResolver::class));

        $default = new DefaultResolver();
        $this->resolver->attach($default);

        $this->assertTrue($this->resolver->hasType(DefaultResolver::class));
    }

    public function testCanRetrieveResolverByType()
    {
        $default = new DefaultResolver();
        $this->resolver->attach($default);
        $this->assertSame($default, $this->resolver->fetchByType(DefaultResolver::class));
    }

    public function testFetchByTypeRaisesExceptionIfResolverNotFound()
    {
        $this->setExpectedException('Phly\Mustache\Exception\ResolverTypeNotFoundException');
        $this->resolver->fetchByType(DefaultResolver::class);
    }

    public function testFetchByTypeReturnsAggregateIfMultipleResolversOfTypeFound()
    {
        $first  = new DefaultResolver();
        $second = new DefaultResolver();

        $this->resolver->attach($first);
        $this->resolver->attach($second);

        $test = $this->resolver->fetchByType(DefaultResolver::class);
        $this->assertInstanceOf(AggregateResolver::class, $test);
        $this->assertNotSame($this->resolver, $test);

        $this->assertQueueContains($first, $test, 'First instance of default resolver not found');
        $this->assertQueueContains($second, $test, 'Second instance of default resolver not found');
    }
}
