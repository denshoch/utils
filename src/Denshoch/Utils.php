<?php

namespace Denshoch;

/**
 * Utility class used in Denshoch softwares.
 */
class Utils
{
    /**
     * Remove Unicode control characters from input text.
     * https://stackoverflow.com/questions/1497885/remove-control-characters-from-php-string
     *
     * @param  string $text Input text
     * @return string output text
     */
    public static function removeCtrlChars( string $text ):string
    {
        $text = str_replace("\xe2\x80\xa8", '', $text);
        $text = str_replace("\xe2\x80\xa9", '', $text);
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    }
}