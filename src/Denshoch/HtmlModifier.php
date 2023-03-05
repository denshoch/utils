<?php

namespace Denshoch;

use DOMDocument;

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
     * @param string $html The HTML string to be modified
     */
    public function __construct(string $html)
    {

        try {
            $this->dom = \Denshoch\Utils::loadXML($html);
        } catch(\Exception $e) {

            $msg = $e->getMessage();
            $reg = '{Extra content at the end of the document in Entity|Start tag expected, \'<\' not found in Entity}';

            if (preg_match($reg, $msg)) {
                $html = '<'.$this->dummyRoot.'>' . $html . '</'.$this->dummyRoot.'>';
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
        if ($this->dom->documentElement->nodeName === $this->dummyRoot) {
            return Utils::innerXML($this->dom->documentElement);
        } else {
            return $this->dom->saveXML($this->dom->documentElement);
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
}
