<?php

namespace Denshoch;

use DOMDocument;
use DOMXPath;
use DOMNode;

/**
 * This is a class to modify HTML with PHP
 */
class HtmlModifier
{
    /**
     * @var DOMDocument The DOMDocument object
     */
    private DOMDocument $dom;

    /**
     * @var DOMDocument Dummy root tag name
     */
    private string $dummyRoot = '_denshoch';

    /**
     * Get the DOMDocument object.
     *
     * @return DOMDocument The DOMDocument object
     */
    public function getDom(): DOMDocument
    {
        return $this->dom;
    }

    /**
     * @param string $html The HTML string to be modified
     */
    public function __construct(string $html)
    {

        try {
            $this->dom = \Denshoch\Utils::loadXML($html);
        } catch (\Exception $e) {

            $msg = $e->getMessage();
            $reg = '{Extra content at the end of the document in Entity|Start tag expected, \'<\' not found in Entity}';

            if (preg_match($reg, $msg)) {
                $html = '<' . $this->dummyRoot . '>' . $html . '</' . $this->dummyRoot . '>';
                $this->dom = \Denshoch\Utils::loadXML($html);
            } else {
                throw $e;
            }
        }

        $this->dom->formatOutput = false;
    }

    /**
     * Add a class to the specified tag
     *
     * @param string $tag The tag to add the class to
     * @param string $class The class to be added
     * @param bool $overwrite Whether to overwrite the existing class or not
     * @return HtmlModifier The HtmlModifier instance (to support method chaining)
     */
    public function addClassToTag(string $tag, string $class, bool $overwrite = false): HtmlModifier
    {
        // エスケープ
        $class = htmlspecialchars($class, ENT_QUOTES | ENT_HTML5);

        // 引数1で指定されたタグを取得する
        $elements = $this->dom->getElementsByTagName($tag);

        // 取得したタグに引数2で指定されたクラス名を追加する
        foreach ($elements as $element) {
            // 既存のクラスがある場合
            if ($element->hasAttribute('class')) {
                // 引数3の値がtrueの場合は、既存のクラスを上書きする
                if ($overwrite) {
                    $element->setAttribute('class', $class);
                } else {

                    // 引数3の値がfalseの場合は、既存のクラスに$classを追記する
                    $current_class = $element->getAttribute('class');
                    $element->setAttribute('class', $current_class . ' ' . $class);
                }
            } else {
                // 既存のクラスがない場合は、$classを追加する
                $element->setAttribute('class', $class);
            }
        }

        // チェーンを可能にするため、HtmlModifierクラスを返す
        return $this;
    }

    /**
     * Save the modified HTML
     *
     * @return string The modified HTML
     */
    public function save(): string
    {
        $nodeName = $this->dom->documentElement->nodeName;
        if ($nodeName === $this->dummyRoot) {
            return Utils::innerXML($this->dom->documentElement);
        } elseif ($nodeName === 'html') {
            return $this->dom->saveXML();
        } else {
            return $this->dom->saveXML($this->dom->documentElement, LIBXML_NOXMLDECL);
        }
    }

    /**
     * Add a class to a tag in the given HTML
     *
     * @param string $html The HTML to be modified
     * @param string $tag The tag to add the class to
     * @param string $class The class to be added
     * @param bool $overwrite Whether to overwrite the existing class or not
     * @return string The modified HTML
     */
    public static function addClass(string $html, string $tag, string $class, bool $overwrite = false): string
    {
        $modifier = new HtmlModifier($html);
        $modifier->addClassToTag($tag, $class, $overwrite);
        return $modifier->save();
    }

    /**
     * Add classes to multiple tags in the given HTML
     *
     * @param string $html The HTML to be modified
     * @param array $tagClassPairs An array of tag-class pairs
     * @param bool $overwrite Whether to overwrite the existing class or not
     * @return string The modified HTML
     */
    public static function addClassMultiple(string $html, array $tagClassPairs, bool $overwrite = false): string
    {

        $modifier = new HtmlModifier($html);

        // タグ名とクラス名のペアを処理する
        foreach ($tagClassPairs as $tag => $class) {
            $modifier->addClassToTag($tag, $class, $overwrite);
        }

        return $modifier->save();
    }

    /**
     * Check if the given node is a descendant of an element with the specified tag name.
     *
     * @param DOMNode $node The node to check
     * @param string $tag The tag name to check for
     * @return bool True if the node is a descendant of an element with the specified tag name, false otherwise
     */
    private function isDescendantOfTag(DOMNode $node, string $tag): bool
    {
        while ($node !== null) {
            if ($node->nodeName === $tag) {
                return true;
            }
            $node = $node->parentNode;
        }

        return false;
    }

    /**
     * Add or update the alt text of an img element with a specified filename in the DOM.
     *
     * @param string $filename The basename of the img src attribute to find and update.
     * @param string $alttxt The alt text to set for the found img element.
     * @param int $override (Optional) Set to 1 to override existing alt text, 0 to leave it unchanged. Default is 0.
     */
    public function addAltText(string $filename, string $alttxt, int $override = 0) {
        $images = $this->dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $basename = basename($src);

            if ($basename === $filename) {
                $alt = $img->getAttribute('alt');

                if ($override === 1) {
                    $img->setAttribute('alt', $alttxt);
                } else {
                    if ($alt === '') {
                        $img->setAttribute('alt', $alttxt);
                    }
                }
            }
        }
    }

    /**
     * Adds ruby annotations to the specified target text within the HTML document.
     *
     * @param string $target The target text to add ruby annotations to
     * @param string $rt The ruby text to be added as annotation
     * @param int $limit The maximum number of occurrences of the target text to be processed (0 means all occurrences)
     * @param bool $rb If true, wrap the target text in an <rb> element; otherwise, use a text node
     * @param bool $rp If true, add <rp>(</rp> before the <rt> element and <rp>)</rp> after the <rt> element
     * @return void
     */
    public function addRubyText(string $target, string $rt, int $limit = 0, bool $rb = false, bool $rp = false): void
    {
        $xpath = new DOMXPath($this->dom);
        $textNodes = $xpath->query("//text()[not(ancestor::head or ancestor::ruby)]");

        $count = 0;
        $pattern = '/(' . preg_quote($target, '/') . ')/u';
        $replacement = function ($matches) use (&$count, $target, $rt, $limit, $rb, $rp) {
            $count++;
    
            if ($limit > 0 && $count > $limit) {
                return $matches[0];
            }
    
            $rubyContent = $rb ? "<rb>{$target}</rb>" : $target;
            $rtElement = "<rt>{$rt}</rt>";
            if ($rp) {
                $rtElement = "<rp>(</rp>{$rtElement}<rp>)</rp>";
            }
    
            return "<ruby>{$rubyContent}{$rtElement}</ruby>";
        };

        foreach ($textNodes as $textNode) {
            if (!$this->isDescendantOfTag($textNode, 'ruby')) {
                $modifiedText = preg_replace_callback($pattern, $replacement, $textNode->nodeValue);

                if ($modifiedText !== $textNode->nodeValue) {
                    $rubyFragment = $this->dom->createDocumentFragment();
                    $rubyFragment->appendXML($modifiedText);
                    $textNode->parentNode->replaceChild($rubyFragment, $textNode);
                }
            }
        }
    }
}
