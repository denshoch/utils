<?php

namespace Denshoch;

use DOMDocument;
use DOMException;
use DOMNode;

/**
 * Utility class used in Denshoch softwares.
 */
class Utils
{
    /**
     * Remove Unicode control characters from input text.
     *
     * @param  string $text Input text
     * @return string Output text
     */
    public static function removeCtrlChars(string $text): string
    {
        $text = str_replace(["\xe2\x80\xa8", "\xe2\x80\xa9"], '', $text);
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    }

    /**
     * Loads an XML string into a DOMDocument object.
     *
     * @param  string $xmlStr The XML string to load
     * @param  int $options (optional) Bitwise OR of the libxml option constants
     * @param  string|null $encoding (optional) The encoding of the XML string
     * @return DOMDocument The DOMDocument object created from the XML string
     * @throws DOMException if an error occurs during the parsing of the XML string
     */
    public static function loadXml(string $xmlStr, int $options = 0, ?string $encoding = null): DOMDocument
    {
        $dom = new DOMDocument();

        if ($encoding !== null) {
            $dom->encoding = $encoding;
        }

        // 外部エンティティと外部DTDの読み込みを無効にする
        $options |= LIBXML_NONET | LIBXML_DTDLOAD;

        $internalErrors = libxml_use_internal_errors(true);
        
        try {
            $xmlStr = self::removeCtrlChars($xmlStr);
            $success = $dom->loadXML($xmlStr, $options);
            
            if (!$success) {
                $errors = libxml_get_errors();
                $errorMessages = array_map(function($error) {
                    return sprintf("Error on line %d: %s", $error->line, $error->message);
                }, $errors);
                throw new DOMException(implode("\n", $errorMessages));
            }
            
            return $dom;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
        }
    }

    /**
     * Get the inner XML of the specified node.
     *
     * @param  DOMNode $node The node to get the inner XML of
     * @return string  The inner XML of the node
     */
    public static function innerXML(DOMNode $node): string
    {
        $xml = '';
        foreach ($node->childNodes as $child) {
            $xml .= $node->ownerDocument->saveXML($child);
        }
        return $xml;
    }
}