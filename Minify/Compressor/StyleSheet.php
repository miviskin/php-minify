<?php

namespace Miviskin\Minify\Compressor;

use Miviskin\Minify\Compressor;

class StyleSheet extends Compressor
{
    /**
     * Compress content.
     *
     * @param  string $content
     * @return string
     */
    protected function compress($content)
    {
        $content = $this->extractStrings($content);
        $content = $this->stripComments($content);
        $content = $this->stripWhitespace($content);
        $content = $this->shortenHex($content);
        $content = $this->restoreExtractedData($content);

        return $content;
    }

    /**
     * Strip comments.
     *
     * @param string $content
     * @return string
     */
    protected function stripComments($content)
    {
        // Multi-line comments
        return preg_replace('~/[*].*?[*]/~s', '', $content);
    }

    /**
     * Strip whitespace.
     *
     * @param  string $content The CSS content to strip the whitespace for.
     * @return string
     */
    protected function stripWhitespace($content)
    {
        // collapse all whitespace into a single space
        $content = preg_replace('~\s+~', ' ', $content);
        // Remove all whitespace around the meta characters
        // inspired by stackoverflow.com/questions/15195750/minify-compress-css-with-regex
        $content = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content);
        // Remove all spaces right of ([: and left of )]
        $content = preg_replace('~([\[(:])\s+~', '$1', $content);
        $content = preg_replace('~\s+([\]\)])~', '$1', $content);
        // Remove spaces around :, except in selectors (where you have to keep a space before it)
        $content = preg_replace('~\s+(:)(?![^\}]*\{)~', '$1', $content);
        // whitespace around + and - can only be stripped in selectors, like
        // :nth-child(3+2n), not in things like calc(3px + 2px) or shorthands
        // like 3px -2px
        $content = preg_replace('~\s*([+-])\s*(?=[^}]*{)~', '$1', $content);
        // remove semicolon/whitespace followed by closing bracket
        $content = preg_replace('~;}~', '}', $content);

        return trim($content);
    }

    /**
     * Shorthand hex color codes.
     * #FF0000 -> #F00
     *
     * @param  string $content The CSS content to shorten the hex color codes for.
     * @return string
     */
    protected function shortenHex($content)
    {
        return preg_replace('~(?<![\'"])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?![\'"])~i', '#$1$2$3', $content);
    }
}
