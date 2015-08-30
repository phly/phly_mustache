<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Phly\Mustache\Exception;
use Traversable;

class PragmaCollection implements
    Countable,
    IteratorAggregate
{
    /**
     * Hash map of pragma names => instances.
     *
     * @var PragmaInterface[]
     */
    private $pragmas = [];

    /**
     * @param PragmaInterface $pragma
     * @throws Exception\DuplicatePragmaException
     */
    public function add(PragmaInterface $pragma)
    {
        if (isset($this->pragmas[$pragma->getName()])) {
            throw new Exception\DuplicatePragmaException();
        }

        $this->pragmas[$pragma->getName()] = $pragma;
    }

    /**
     * @param string $pragma
     * @return bool
     */
    public function has($pragma)
    {
        return isset($this->pragmas[$pragma]);
    }

    /**
     * @param string $pragma
     * @return PragmaInterface
     * @throws Exception\PragmaNotFoundException
     */
    public function get($pragma)
    {
        if (! isset($this->pragmas[$pragma])) {
            throw new Exception\PragmaNotFoundException();
        }

        return $this->pragmas[$pragma];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->pragmas);
    }

    /**
     * Clears all pragmas from the collection.
     */
    public function clear()
    {
        $this->pragmas = [];
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->pragmas);
    }
}
