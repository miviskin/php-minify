<?php

namespace Miviskin\Minify;

class CompressorResolver
{
    /**
     * Compressor resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * Register a new compressor resolver.
     *
     * @param  string   $name
     * @param  \Closure $resolver
     * @return $this
     */
    public function register($name, \Closure $resolver)
    {
        $this->resolvers[$name] = $resolver;

        return $this;
    }

    /**
     * Resolve compressor by name.
     *
     * @param  string  $name
     * @return Compressor
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name)
    {
        if (!$this->exists($name)) {
            throw new \InvalidArgumentException(
                sprintf('Compressor %s not found.', $name)
            );
        }

        return call_user_func($this->resolvers[$name]);
    }

    /**
     * Determine compressor exists.
     *
     * @param  string  $compressor
     * @return bool
     */
    public function exists($compressor)
    {
        return array_key_exists($compressor, $this->resolvers);
    }
}
