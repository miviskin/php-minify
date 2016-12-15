<?php

namespace Miviskin\Minify;

use Illuminate\Filesystem\Filesystem;

class Factory
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The compressor resolver instance.
     *
     * @var CompressorResolver
     */
    protected $compressorResolver;

    /**
     * The extension to compressor bindings.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Factory constructor.
     *
     * @param CompressorResolver $compressorResolver
     * @param Filesystem $filesystem
     */
    public function __construct(CompressorResolver $compressorResolver, Filesystem $filesystem)
    {
        $this->compressorResolver = $compressorResolver;
        $this->filesystem = $filesystem;
    }

    /**
     * Get the evaluated contents for the given data.
     *
     * @param  Compressor $compressor
     * @param  mixed $data
     * @return Minify
     */
    public function make(Compressor $compressor, $data = '')
    {
        return new Minify($this->filesystem, $compressor, $data);
    }

    /**
     * Add new file extension compressor.
     *
     * @param $extension
     * @param $compressor
     * @param \Closure|null $resolver
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addExtension($extension, $compressor, \Closure $resolver = null)
    {
        if ($resolver !== null) {
            $this->compressorResolver->register($compressor, $resolver);
        } elseif (!in_array($compressor, $this->extensions)) {
            throw new \InvalidArgumentException(
                sprintf('Compressor %s is not registered.', $compressor)
            );
        }

        $this->extensions[$extension] = $compressor;

        return $this;
    }

    /**
     * Get file extensions with associated compressors.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get the compressor resolver instance.
     *
     * @return CompressorResolver
     */
    public function getCompressorResolver()
    {
        return $this->compressorResolver;
    }

    /**
     * Magic get the evaluated contents.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Minify
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters = [])
    {
        if (array_key_exists($method, $this->extensions)) {
            return $this->make($this->compressorResolver->resolve($this->extensions[$method]), $parameters);
        }

        throw new \BadMethodCallException(
            sprintf('Method %s does not exist in %s', $method, get_class($this))
        );
    }
}
