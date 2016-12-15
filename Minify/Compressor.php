<?php

namespace Miviskin\Minify;

abstract class Compressor implements CompressorInterface
{
    /**
     * The data to compress.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Extracted strings from data.
     *
     * @var array
     */
    protected $extracted = [];

    /**
     * Compressed data.
     *
     * @var string
     */
    protected $compressed = '';

    /**
     * Get compressed data.
     *
     * @return string
     */
    public function get()
    {
        if (count($this->data)) {
            $this->compressed .= $this->compress(implode('', $this->data));
            $this->data = [];
        }

        return $this->compressed;
    }

    /**
     * Add data to compress.
     *
     * @param  string $data
     * @return $this
     */
    public function add($data)
    {
        $this->data[] = (string) $data;
    }

    /**
     * Compress data.
     *
     * @param  string $data
     * @return string
     */
    abstract protected function compress($data);

    /**
     * Extract strings from data.
     *
     * @param string $content
     * @return string
     */
    protected function extractStrings($content)
    {
        $callback = function($match) {
            if (!$match[2]) {
                // Empty strings need no placeholder;
                return $match[0];
            }

            $placeholder = $match[1] . count($this->extracted) . $match[1];

            $this->extracted[$placeholder] = $match[1] . $match[2] . $match[1];

            return $placeholder;
        };

        /*
         * The \\ messiness explained:
         * * Don't count ' or " as end-of-string if it's escaped (has backslash
         * in front of it)
         * * Unless... that backslash itself is escaped (another leading slash),
         * in which case it's no longer escaping the ' or "
         * * So there can be either no backslash, or an even number
         * * multiply all of that times 4, to account for the escaping that has
         * to be done to pass the backslash into the PHP string without it being
         * considered as escape-char (times 2) and to get it in the regex,
         * escaped (times 2)
         */
        $content = preg_replace_callback('~(\')((?:\\\\.|[^\'\\\\])*+)\'~s', $callback, $content);
        $content = preg_replace_callback('~(\")((?:\\\\.|[^\"\\\\])*+)\"~s', $callback, $content);

        return $content;
    }

    /**
     * This method will restore all extracted data (strings, regexes) that were
     * replaced with placeholder text in extract*(). The original content was
     * saved in $this->extracted.
     *
     * @param  string $content
     * @return string
     */
    protected function restoreExtractedData($content)
    {
        if (!$this->extracted) {
            // nothing was extracted, nothing to restore
            return $content;
        }

        $content = strtr($content, $this->extracted);

        $this->extracted = [];

        return $content;
    }

    /**
     * Get the string contents of the compressor
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }
}
