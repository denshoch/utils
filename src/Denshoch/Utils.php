<?php

namespace Denshoch;

use DOMDocument;

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
     * @return string Output text
     */
    public static function removeCtrlChars(string $text): string
    {
        $text = str_replace("\xe2\x80\xa8", '', $text);
        $text = str_replace("\xe2\x80\xa9", '', $text);
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    }

    /**
     * Error handler for loadXml function.
     * https://www.php.net/manual/ja/domdocument.loadxml.php
     *
     * @param  int    $errno  The level of the error raised
     * @param  string $errstr The error message
     * @param  string $errfile The filename that the error was raised in
     * @param  int    $errline The line number the error was raised at
     * @throws \DOMException if the error level is E_WARNING and the error message contains "DOMDocument::loadXML()"
     */
    public static function handleXmlError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
            throw new \DOMException($errstr);
        }
    }

    /**
     * Loads an XML string into a DOMDocument object.
     *
     * @param  string $xmlStr The XML string to load
     * @return DOMDocument The DOMDocument object created from the XML string
     * @throws \DOMException if an error occurs during the parsing of the XML string
     */
    public static function loadXml(string $xmlStr): \DOMDocument
    {
        set_error_handler([__CLASS__, 'handleXmlError']);
        $dom = new \DOMDocument();
        $dom->loadXml($xmlStr);
        restore_error_handler();
        return $dom;
    }

    /**
     * Get the inner XML of the specified node.
     *
     * @param  DOMNode $node The node to get the inner XML of
     * @return string  The inner XML of the node
     */
    public static function innerXML(\DOMNode $node): string
    {
        $xml = '';

        // 子ノードを取得してXML文字列を連結する
        foreach ($node->childNodes as $child) {
            $xml .= $node->ownerDocument->saveXML($child);
        }

        return $xml;
    }
}
