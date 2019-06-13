<?php

use Recca0120\LaravelBridge\Laravel;

if (! function_exists('app')) {
    /**
     * Get the Laravel Bridge instance.
     *
     * @param  string|null  $abstract
     * @param  array   $parameters
     * @return mixed|Laravel
     */
    function app($abstract = null, array $parameters = [])
    {
        if (null === $abstract) {
            return Laravel::createInstance();
        }

        return Laravel::createInstance()->make($abstract, $parameters);
    }
}
