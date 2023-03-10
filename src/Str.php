<?php


namespace NaturalCloud\Collection;

use InvalidArgumentException;
use NaturalCloud\Collection\Macroable\Macroable;


/**
 * Most of the methods in this file come from illuminate/support,
 * thanks Laravel Team provide such a useful class.
 */
class Str
{
    use Macroable;

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function afterLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string)$search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Get the portion of a string before a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function before($subject, $search)
    {
        return $search === '' ? $subject : explode($search, $subject)[0];
    }

    /**
     * Get the portion of a string between two given values.
     *
     * @param string $subject
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function between($subject, $from, $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return static::substr($subject, 0, $pos);
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param string $string
     * @param int $start
     * @param null|int $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Return the remainder of a string after a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Convert a value to camel case.
     *
     * @param string $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly caps case.
     */
    public static function studly(string $value, string $gap = ''): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', $gap, $value);
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param string $haystack
     * @param string[] $needles
     * @return bool
     */
    public static function containsAll($haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if (!static::contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param array|string $pattern
     * @param string $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a string to kebab case.
     *
     * @param string $value
     * @return string
     */
    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

    /**
     * Convert a string to snake case.
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param string $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    /**
     * Limit the number of words in a string.
     */
    public static function words(string $value, int $words = 100, string $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0]) || static::length($value) === static::length($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    /**
     * Return the length of the given string.
     *
     * @param string $value
     * @param string $encoding
     * @return int
     */
    public static function length($value, $encoding = null)
    {
        if ($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param string $pattern
     * @param string $subject
     * @return string
     */
    public static function match($pattern, $subject)
    {
        preg_match($pattern, $subject, $matches);

        if (!$matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param string $pattern
     * @param string $subject
     * @return Collection
     */
    public static function matchAll($pattern, $subject)
    {
        preg_match_all($pattern, $subject, $matches);

        if (empty($matches[0])) {
            return new Collection();
        }

        return new Collection($matches[1] ?? $matches[0]);
    }

    /**
     * Pad both sides of a string with another.
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padBoth($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_BOTH);
    }

    /**
     * Pad the left side of a string with another.
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padLeft($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_LEFT);
    }

    /**
     * Pad the right side of a string with another.
     *
     * @param string $value
     * @param int $length
     * @param string $pad
     * @return string
     */
    public static function padRight($value, $length, $pad = ' ')
    {
        return str_pad($value, $length, $pad, STR_PAD_RIGHT);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Repeat the given string.
     *
     * @return string
     */
    public static function repeat(string $string, int $times)
    {
        return str_repeat($string, $times);
    }

    /**
     * Replace a given value in the string sequentially with an array.
     */
    public static function replaceArray(string $search, array $replace, string $subject): string
    {
        foreach ($replace as $value) {
            $subject = static::replaceFirst($search, (string)$value, $subject);
        }

        return $subject;
    }

    /**
     * Replace the first occurrence of a given value in the string.
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the given value in the given string.
     *
     * @param string|string[] $search
     * @param string|string[] $replace
     * @param string|string[] $subject
     * @return string
     */
    public static function replace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Replace the last occurrence of a given value in the string.
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Remove any occurrence of the given string in the subject.
     *
     * @param array<string>|string $search
     * @param string $subject
     * @param bool $caseSensitive
     * @return string
     */
    public static function remove($search, $subject, $caseSensitive = true)
    {
        return $caseSensitive
            ? str_replace($search, '', $subject)
            : str_ireplace($search, '', $subject);
    }

    /**
     * Begin a string with a single instance of a given value.
     */
    public static function start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    /**
     * Strip HTML and PHP tags from the given string.
     *
     * @param null|string|string[] $allowedTags
     */
    public static function stripTags(string $value, $allowedTags = null): string
    {
        return strip_tags($value, $allowedTags);
    }

    /**
     * Convert the given string to title case.
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     */
    public static function slug(string $title, string $separator = '-', string $language = 'en'): string
    {
        $title = $language ? static::ascii($title, $language) : $title;

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator . 'at' . $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param string $value
     * @param string $language
     * @return string
     */
    public static function ascii($value, $language = 'en')
    {
        $languageSpecific = static::languageSpecificCharsArray($language);

        if (!is_null($languageSpecific)) {
            $value = str_replace($languageSpecific[0], $languageSpecific[1], $value);
        }

        foreach (static::charsArray() as $key => $val) {
            $value = str_replace($val, (string)$key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Returns the language specific replacements for the ascii method.
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     * @return null|array
     */
    protected static function languageSpecificCharsArray(string $language)
    {
        static $languageSpecific;

        if (!isset($languageSpecific)) {
            $languageSpecific = [
                'bg' => [
                    ['??', '??', '??', '??', '??', '??', '??', '??'],
                    ['h', 'H', 'sht', 'SHT', 'a', '??', 'y', 'Y'],
                ],
                'de' => [
                    ['??', '??', '??', '??', '??', '??'],
                    ['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
                ],
            ];
        }

        return $languageSpecific[$language] ?? null;
    }

    /**
     * Returns the replacements for the ascii method.
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     */
    protected static function charsArray(): array
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0'    => ['??', '???', '??', '???'],
            '1'    => ['??', '???', '??', '???'],
            '2'    => ['??', '???', '??', '???'],
            '3'    => ['??', '???', '??', '???'],
            '4'    => ['???', '???', '??', '??', '???'],
            '5'    => ['???', '???', '??', '??', '???'],
            '6'    => ['???', '???', '??', '??', '???'],
            '7'    => ['???', '???', '??', '???'],
            '8'    => ['???', '???', '??', '???'],
            '9'    => ['???', '???', '??', '???'],
            'a'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '???',
                '???',
                '??',
                '???',
                '??',
            ],
            'b'    => ['??', '??', '??', '???', '???', '???'],
            'c'    => ['??', '??', '??', '??', '??', '???'],
            'd'    => ['??', '??', '??', '??', '??', '??', '??', '???', '???', '???', '??', '??', '??', '??', '???', '???', '???', '???'],
            'e'    => [
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '???',
            ],
            'f'    => ['??', '??', '??', '??', '???', '???'],
            'g'    => ['??', '??', '??', '??', '??', '??', '??', '???', '???', '??', '???'],
            'h'    => ['??', '??', '??', '??', '??', '??', '???', '???', '???', '???'],
            'i'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '??????',
                '??',
                '???',
                '???',
                '??',
                '???',
            ],
            'j'    => ['??', '??', '??', '???', '??', '???'],
            'k'    => ['??', '??', '??', '??', '??', '??', '??', '???', '???', '???', '??', '???'],
            'l'    => ['??', '??', '??', '??', '??', '??', '??', '??', '???', '???', '???'],
            'm'    => ['??', '??', '??', '???', '???', '???'],
            'n'    => ['??', '??', '??', '??', '??', '??', '??', '??', '??', '???', '???', '???'],
            'o'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??????',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
            ],
            'p'    => ['??', '??', '???', '???', '??', '???'],
            'q'    => ['???', '???'],
            'r'    => ['??', '??', '??', '??', '??', '??', '???', '???'],
            's'    => ['??', '??', '??', '??', '??', '??', '??', '??', '??', '???', '??', '???', '???'],
            't'    => ['??', '??', '??', '??', '??', '??', '??', '???', '???', '??', '???', '???', '???'],
            'u'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
                '??',
            ],
            'v'    => ['??', '???', '??', '???'],
            'w'    => ['??', '??', '??', '???', '???', '???'],
            'x'    => ['??', '??', '???'],
            'y'    => ['??', '???', '???', '???', '???', '??', '??', '??', '??', '??', '??', '??', '??', '??', '???', '???'],
            'z'    => ['??', '??', '??', '??', '??', '??', '???', '???', '???'],
            'aa'   => ['??', '???', '??'],
            'ae'   => ['??', '??'],
            'ai'   => ['???'],
            'ch'   => ['??', '???', '???', '??'],
            'dj'   => ['??', '??'],
            'dz'   => ['??', '???'],
            'ei'   => ['???'],
            'gh'   => ['??', '???'],
            'ii'   => ['???'],
            'ij'   => ['??'],
            'kh'   => ['??', '??', '???'],
            'lj'   => ['??'],
            'nj'   => ['??'],
            'oe'   => ['??', '??', '??'],
            'oi'   => ['???'],
            'oii'  => ['???'],
            'ps'   => ['??'],
            'sh'   => ['??', '???', '??'],
            'shch' => ['??'],
            'ss'   => ['??'],
            'sx'   => ['??'],
            'th'   => ['??', '??', '??', '??', '??'],
            'ts'   => ['??', '???', '???'],
            'ue'   => ['??'],
            'uu'   => ['???'],
            'ya'   => ['??'],
            'yu'   => ['??'],
            'zh'   => ['??', '???', '??'],
            '(c)'  => ['??'],
            'A'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '??',
                '??',
                '??',
                '???',
                '??',
            ],
            'B'    => ['??', '??', '???', '???'],
            'C'    => ['??', '??', '??', '??', '??', '???'],
            'D'    => ['??', '??', '??', '??', '??', '??', '???', '???', '??', '??', '???'],
            'E'    => [
                '??',
                '??',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
            ],
            'F'    => ['??', '??', '???'],
            'G'    => ['??', '??', '??', '??', '??', '??', '???'],
            'H'    => ['??', '??', '??', '???'],
            'I'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
            ],
            'J'    => ['???'],
            'K'    => ['??', '??', '???'],
            'L'    => ['??', '??', '??', '??', '??', '??', '??', '???', '???'],
            'M'    => ['??', '??', '???'],
            'N'    => ['??', '??', '??', '??', '??', '??', '??', '???'],
            'O'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '??',
            ],
            'P'    => ['??', '??', '???'],
            'Q'    => ['???'],
            'R'    => ['??', '??', '??', '??', '??', '???'],
            'S'    => ['??', '??', '??', '??', '??', '??', '??', '???'],
            'T'    => ['??', '??', '??', '??', '??', '??', '???'],
            'U'    => [
                '??',
                '??',
                '???',
                '??',
                '???',
                '??',
                '???',
                '???',
                '???',
                '???',
                '???',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '??',
                '???',
                '??',
                '??',
            ],
            'V'    => ['??', '???'],
            'W'    => ['??', '??', '??', '???'],
            'X'    => ['??', '??', '???'],
            'Y'    => ['??', '???', '???', '???', '???', '??', '???', '???', '???', '??', '??', '??', '??', '??', '??', '???'],
            'Z'    => ['??', '??', '??', '??', '??', '???'],
            'AE'   => ['??', '??'],
            'Ch'   => ['??'],
            'Dj'   => ['??'],
            'Dz'   => ['??'],
            'Gx'   => ['??'],
            'Hx'   => ['??'],
            'Ij'   => ['??'],
            'Jx'   => ['??'],
            'Kh'   => ['??'],
            'Lj'   => ['??'],
            'Nj'   => ['??'],
            'Oe'   => ['??'],
            'Ps'   => ['??'],
            'Sh'   => ['??'],
            'Shch' => ['??'],
            'Ss'   => ['???'],
            'Th'   => ['??'],
            'Ts'   => ['??'],
            'Ya'   => ['??'],
            'Yu'   => ['??'],
            'Zh'   => ['??'],
            ' '    => [
                "\xC2\xA0",
                "\xE2\x80\x80",
                "\xE2\x80\x81",
                "\xE2\x80\x82",
                "\xE2\x80\x83",
                "\xE2\x80\x84",
                "\xE2\x80\x85",
                "\xE2\x80\x86",
                "\xE2\x80\x87",
                "\xE2\x80\x88",
                "\xE2\x80\x89",
                "\xE2\x80\x8A",
                "\xE2\x80\xAF",
                "\xE2\x81\x9F",
                "\xE3\x80\x80",
                "\xEF\xBE\xA0",
            ],
        ];
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param array|string $needles
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the number of substring occurrences.
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param null|int $length
     * @return int
     */
    public static function substrCount($haystack, $needle, $offset = 0, $length = null)
    {
        if (!is_null($length)) {
            return substr_count($haystack, $needle, $offset, $length);
        }
        return substr_count($haystack, $needle, $offset);
    }

    /**
     * Make a string's first character uppercase.
     */
    public static function ucfirst(string $string): string
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    /**
     * Convert the given string to upper-case.
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Replaces the first or the last ones chars from a string by a given char.
     *
     * @param int $offset if is negative it starts from the end
     * @param string $replacement default is *
     */
    public static function mask(string $string, int $offset = 0, int $length = 0, string $replacement = '*')
    {
        if ($length < 0) {
            throw new InvalidArgumentException('The length must equal or greater than zero.');
        }

        $stringLength = mb_strlen($string);
        $absOffset = abs($offset);
        if ($absOffset >= $stringLength) {
            return $string;
        }

        $hiddenLength = $length ?: $stringLength - $absOffset;

        if ($offset >= 0) {
            return mb_substr($string, 0, $offset) . str_repeat($replacement, $hiddenLength) . mb_substr($string,
                    $offset + $hiddenLength);
        }

        return mb_substr($string, 0, max($stringLength - $hiddenLength - $absOffset, 0)) . str_repeat($replacement,
                $hiddenLength) . mb_substr($string, $offset);
    }
}
