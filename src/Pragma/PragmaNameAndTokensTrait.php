<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Pragma;

trait PragmaNameAndTokensTrait
{
    /**
     * Retrieve the name of the pragma
     *
     * @return string
     * @throws Exception\MissingPragmaNameException
     */
    public function getName()
    {
        if (! isset($this->name) || empty($this->name)) {
            throw new Exception\MissingPragmaNameException();
        }

        return $this->name;
    }

    /**
     * Whether or not this pragma can handle the given token.
     *
     * @param  int $token
     * @return bool
     */
    public function handlesToken($token)
    {
        return (
            isset($this->tokensHandled)
            && is_array($this->tokensHandled)
            && in_array($token, $this->tokensHandled)
        );
    }
}
