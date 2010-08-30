<?php

namespace Phly\Mustache;

interface Pragma
{
    /**
     * Retrieve the name of the pragma
     * 
     * @return string
     */
    public function getName();

    /**
     * Set the renderer instance
     * 
     * @param  Renderer $renderer 
     * @return void
     */
    public function setRenderer(Renderer $renderer);

    /**
     * Whether or not this pragma can handle the given token
     * 
     * @param  int $token 
     * @return bool
     */
    public function handlesToken($token);

    /**
     * Handle a given token
     *
     * Returning an empty value returns control to the renderer.
     * 
     * @param  int $token 
     * @param  mixed $data 
     * @param  mixed $view 
     * @param  array $options 
     * @return mixed
     */
    public function handle($token, $data, $view, array $options);
}
