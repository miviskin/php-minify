<?php

namespace Miviskin\Minify\Compressor;

use Miviskin\Minify\Compressor;

class JavaScript extends Compressor
{
    /**
     * Don't have [+], [-], [.]
     *
     * @var array
     */
    protected $operators = [
        '=',  '+=', '-=',  '*=',  '/=', '%=', '<<=', '>>=', '>>>=', '&=', '^=', '|=',
        '==', '!=', '===', '!==', '>',  '>=', '<',   '<=',
        '&',  '|',  '^',   '~',   '<<', '>>', '>>>',
        '[',  ']',  '(',   ')',   '{',  '}',
        '&&', '||', '!',
        '*',  '/',  '%',
        '?',  ':',
        ',',  ';',
    ];

    /**
     * @var array
     */
    protected $keywordsBefore = [
        'do',
        'let', 'new', 'var',
        'else', 'case', 'void', 'enum',
        'break', 'class', 'const', 'throw', 'yield', 'await',
        'static', 'delete', 'export', 'import', 'return', 'typeof',
        'package', 'continue', 'function', 'interface',
    ];

    /**
     * @var array
     */
    protected $keywordsAround = [
        'in',
        'public',
        'extends',
        'private',
        'protected',
        'implements',
        'instanceof',
    ];

    /**
     * @var array
     */
    protected $keywordsReserved = [];

    /**
     * Compress content.
     *
     * @param  string  $content
     * @return string
     */
    protected function compress($content)
    {
        $content = $this->stripContinue($content);
        $content = $this->extractStrings($content);
        $content = $this->stripComments($content);
        $content = $this->extractRegex($content);
        $content = $this->stripWhitespace($content);
        $content = $this->shortenBools($content);
        $content = $this->restoreExtractedData($content);

        return $content;
    }

    /**
     * Strip continue char in end line.
     *
     * @param string $content
     * @return string
     */
    protected function stripContinue($content)
    {
        return preg_replace('~\s*\\\\\r?\n\s*~', '\n', $content);
    }

    /**
     * JS can have /-delimited regular expressions, like: /ab+c/.match(string)
     *
     * The content inside the regex can contain characters that may be confused
     * for JS code: e.g. it could contain whitespace it needs to match & we
     * don't want to strip whitespace in there.
     *
     * The regex can be pretty simple: we don't have to care about comments,
     * (which also use slashes) because stripComments() will have stripped those
     * already.
     *
     * This method will replace all string content with simple REGEX#
     * placeholder text, so we've rid all regular expressions from characters
     * that may be misinterpreted. Original regex content will be saved in
     * $this->extracted and after doing all other minifying, we can restore the
     * original content via restoreRegex()
     *
     * @param string $content
     * @return string
     */
    protected function extractRegex($content)
    {
        $callback = function ($match) {
            $placeholder = '/' . count($this->extracted) . '/';
            $this->extracted[$placeholder] = $match[1];
            return $placeholder;
        };

        $diff = array_diff($this->operators, [']', ')', '}']);

        $escaped = array_map('preg_quote', $diff, array_fill(0, count($diff), '~'));

        // it's a regex if we can find an opening (not preceded by variable,
        // value or similar) & (non-escaped) closing /,
        return preg_replace_callback('~(?:^|' . implode('|', $escaped) . ')\s*+\K(/(?!=)(?:\\\\.|[^/\\\\])*+/)~', $callback, $content);
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
        $content = preg_replace('~(?<!\\\\)/[*].*?[*]/~s', '', $content);

        // Single-line comments
        $content = preg_replace('~(?<!\\\\)//.*$~m', '', $content);

        return $content;
    }

    /**
     * Strip whitespace.
     *
     * We won't strip *all* whitespace, but as much as possible. The thing that
     * we'll preserve are newlines we're unsure about.
     * JavaScript doesn't require statements to be terminated with a semicolon.
     * It will automatically fix missing semicolons with ASI (automatic semi-
     * colon insertion) at the end of line causing errors (without semicolon.)
     *
     * Because it's sometimes hard to tell if a newline is part of a statement
     * that should be terminated or not, we'll just leave some of them alone.
     *
     * @param  string $content The content to strip the whitespace for.
     * @return string
     */
    protected function stripWhitespace($content)
    {
        // collapse all whitespace into a single space
        $content = preg_replace('~\s+~', ' ', $content);

        // strip whitespace around operators
        $escaped = array_map('preg_quote', $this->operators, array_fill(0, count($this->operators), '~'));
        $content = preg_replace('~\s*(' . implode('|', $escaped) . ')\s*~', '\\1', $content);

        // make sure + and - can't be mistaken for, or joined into ++ and --
        $content = preg_replace('~(?<![+-])\s*([+-])(?![+-])~', '\\1', $content);
        $content = preg_replace('~(?<![+-])([+-])\s*(?![+-])~', '\\1', $content);

        // collapse whitespace around reserved words into single space
        //$content = preg_replace('~\b('  . implode('|', $this->keywordsBefore) . ')\s+~', '\\1 ', $content);
        //$content = preg_replace('~\s+(' . implode('|', $this->keywordsAround) . ')\s+~', ' \\1 ', $content);

        /*
         * We also don't really want to terminate statements followed by closing
         * curly braces (which we've ignored completely up until now) or end-of-
         * script: ASI will kick in here & we're all about minifying.
         * Semicolons at beginning of the file don't make any sense either.
         */
        $content = preg_replace('~[,;](\}|\])~', '\\1', $content);

        // get rid of remaining whitespace af beginning/end
        return trim($content);
    }

    /**
     * Replaces true & false by !0 and !1.
     *
     * @param  string $content
     * @return string
     */
    protected function shortenBools($content)
    {
        $content = preg_replace('~\btrue\b~', '!0', $content);
        $content = preg_replace('~\bfalse\b~', '!1', $content);

        return $content;
    }
}
