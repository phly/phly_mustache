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
     * Construcor.
     *
     * Injects ImplicitIterator by default.
     */
    public function __construct()
    {
        $this->add(new ImplicitIterator());
    }

    public function add(PragmaInterface $pragma)
    {
        if (isset($this->pragmas[$pragma->getName()])) {
            throw new Exception\DuplicatePragmaException();
        }

        $this->pragmas[$pragma->getName()] = $pragma;
    }

    public function has($pragma)
    {
        return isset($this->pragmas[$pragma]);
    }

    public function get($pragma)
    {
        if (! isset($this->pragmas[$pragma])) {
            throw new Exception\PragmaNotFoundException();
        }

        return $this->pragmas[$pragma];
    }

    public function count()
    {
        return count($this->pragmas);
    }

    public function clear()
    {
        $this->pragmas = [];
    }

    public function getIterator()
    {
        return new ArrayIterator($this->pragmas);
    }
}
