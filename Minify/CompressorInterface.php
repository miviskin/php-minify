<?php

namespace Miviskin\Minify;

interface CompressorInterface
{
    /**
     * Get compressed data.
     *
     * @return string
     */
    public function get();

    /**
     * Add data to compress.
     *
     * @param  string $data
     * @return $this
     */
    public function add($data);
}
