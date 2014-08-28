<?php namespace Clowdy\Raven;

use Illuminate\Log\Writer;
use Exception;
use Closure;

/**
 * Overrides default Logger to provide extra functionality.
 */
class Log extends Writer
{
    /**
	 * Dynamically handle error additions.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->levels)) {
            // Handle exceptions using context
            // Provides a nice wrapper around default logging methods
            if (count($parameters) >= 1 && is_a($parameters[0], 'Exception')) {
                // Create context if none is passed
                if (!isset($parameters[1])) {
                    $parameters[1] = [];
                }

                // Set the exception context
                $parameters[1]['exception'] = $parameters[0];

                // Set message using exception
                $parameters[0] = $parameters[0]->getMessage();
            }

            call_user_func_array([$this, 'fireLogEvent'], array_merge([$method], $parameters));

            $method = 'add'.ucfirst($method);

            return $this->callMonolog($method, $parameters);
        }

        throw new \BadMethodCallException("Method [$method] does not exist.");
    }

    /**
	 * Register a new Monolog handler.
	 *
	 * @param string   $level   Laravel log level.
	 * @param \Closure $closure Return an instance of \Monolog\Handler\HandlerInterface.
	 *
	 * @throws \InvalidArgumentException Unknown log level.
	 *
	 * @return bool Whether handler was registered.
	 */
    public function registerHandler($level, Closure $callback)
    {
        $level   = $this->parseLevel($level);
        $handler = call_user_func($callback, $level);

        // Add handler to Monolog
        $this->getMonolog()->pushHandler($handler);

        return true;
    }
}
