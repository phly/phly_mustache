<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace PhlyTest\Mustache\Pragma;

use Phly\Mustache\Pragma;
use PHPUnit_Framework_TestCase as TestCase;

class PragmaCollectionTest extends TestCase
{
    public function setUp()
    {
        $this->collection = new Pragma\PragmaCollection();
    }

    public function testCollectionIsCountable()
    {
        $this->assertInstanceOf('Countable', $this->collection);
    }

    public function testCollectionIsIterable()
    {
        $this->assertInstanceOf('Traversable', $this->collection);
    }

    public function testComposesNoPragmasByDefault()
    {
        $this->assertCount(0, $this->collection);
    }

    public function testCanAddPragmas()
    {
        $originalCount = count($this->collection);
        $pragma = $this->prophesize(Pragma\PragmaInterface::class);
        $pragma->getName()->willReturn('TEST-PRAGMA');
        $this->collection->add($pragma->reveal());
        $this->assertCount($originalCount + 1, $this->collection);
        $this->assertTrue($this->collection->has('TEST-PRAGMA'));
        $test = $this->collection->get('TEST-PRAGMA');
        $this->assertSame($pragma->reveal(), $test);
    }

    public function testCanRemoveAllPragmas()
    {
        $this->collection->add(new Pragma\ImplicitIterator());
        $this->assertCount(1, $this->collection);
        $this->collection->clear();
        $this->assertCount(0, $this->collection);
    }

    public function testCannotAddTwoPragmasAdvertisingSameName()
    {
        $pragma1 = $this->prophesize(Pragma\PragmaInterface::class);
        $pragma1->getName()->willReturn('TEST-PRAGMA');
        $pragma2 = $this->prophesize(Pragma\PragmaInterface::class);
        $pragma2->getName()->willReturn('TEST-PRAGMA');

        $this->collection->add($pragma1->reveal());
        $this->setExpectedException('Phly\Mustache\Exception\DuplicatePragmaException');
        $this->collection->add($pragma2->reveal());
    }
}
