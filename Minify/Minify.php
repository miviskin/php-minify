<?php

namespace Miviskin\Minify;

use Illuminate\Filesystem\Filesystem;

class Minify
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The compressor implementation.
     *
     * @var CompressorInterface
     */
    protected $compressor;

    /**
     * Minify constructor.
     *
     * @param Filesystem $filesystem
     * @param CompressorInterface $compressor
     * @param string $data
     */
    public function __construct(Filesystem $filesystem, CompressorInterface $compressor, $data = '')
    {
        $this->filesystem = $filesystem;
        $this->compressor = $compressor;
        $this->append($data);
    }

    /**
     * Append data.
     *
     * @param  mixed  $data
     * @return $this
     */
    public function append($data)
    {
        if ($data !== '') {
            $data = is_array($data) ? $data : [$data];

            foreach ($data as $value) {
                if (is_array($value)) {
                    $this->add($value);
                } else {
                    $this->isFile($value) ? $this->addFile($value) : $this->appendData($value);
                }
            }
        }

        return $this;
    }

    /**
     * Determine if the given data is a file.
     *
     * @param  string $data
     * @return bool
     */
    protected function isFile($data)
    {
        return (strpos($data, '/') === 0 && strpos($data, "\n") === false) ? $this->filesystem->isFile($data) : false;
    }

    /**
     * Add files content to data.
     *
     * @param  array $files
     * @return $this
     */
    public function appendFiles($files)
    {
        foreach ($files as $file) {
            $this->appendFile($file);
        }

        return $this;
    }

    /**
     * Add file content to compressor.
     *
     * @param  string $path
     * @return $this
     */
    public function appendFile($path)
    {
        $this->appendData($this->filesystem->get($path));

        return $this;
    }

    /**
     * Add data to compressor.
     *
     * @param  mixed $data
     * @return $this
     */
    public function appendData($data)
    {
        $data = is_array($data) ? $data : [$data];

        foreach ($data as $value) {
            if (is_array($value)) {
                $this->appendData($value);
            } else {
                $this->compressor->add($value);
            }
        }

        return $this;
    }

    /**
     * Get the compressor instance.
     *
     * @return CompressorInterface
     */
    public function getCompressor()
    {
        return $this->compressor;
    }

    /**
     * Get compressed data.
     *
     * @return string
     */
    public function get()
    {
        return $this->compressor->get();
    }

    /**
     * Get the string contents of the minify.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }
}
