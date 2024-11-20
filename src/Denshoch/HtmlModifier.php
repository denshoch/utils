<?php

namespace Denshoch;

use DOMDocument;
use DOMXPath;
use DOMNode;
use InvalidArgumentException;

/**
 * This is a class to modify HTML with PHP
 */
class HtmlModifier
{
    private DOMDocument $dom;
    private string $dummyRoot = '_denshoch';

    /**
     * @param string $html The HTML string to be modified
     * @throws InvalidArgumentException If the HTML is invalid
     */
    public function __construct(string $html)
    {
        $this->loadHtml($html);
        $this->dom->formatOutput = false;
    }

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
     * Load HTML into DOMDocument
     *
     * @param string $html
     * @throws InvalidArgumentException
     */
    private function loadHtml(string $html): void
    {
        try {
            $this->dom = Utils::loadXML($html);
        } catch (\Exception $e) {
            if ($this->isExtraContentError($e)) {
                $html = "<{$this->dummyRoot}>{$html}</{$this->dummyRoot}>";
                $this->dom = Utils::loadXML($html);
            } else {
                throw new InvalidArgumentException("Invalid HTML: " . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Check if the exception is due to extra content
     *
     * @param \Exception $e
     * @return bool
     */
    private function isExtraContentError(\Exception $e): bool
    {
        $msg = $e->getMessage();
        $reg = '{Extra content at the end of the document|Start tag expected, \'<\' not found}';
        return preg_match($reg, $msg) === 1;
    }

    /**
     * Add a class to the specified tag
     *
     * @param string $tag The tag to add the class to
     * @param string $class The class to be added
     * @param bool $overwrite Whether to overwrite the existing class or not
     * @return $this
     */
    public function addClassToTag(string $tag, string $class, bool $overwrite = false): self
    {
        $class = htmlspecialchars($class, ENT_QUOTES | ENT_HTML5);
        $elements = $this->dom->getElementsByTagName($tag);

        foreach ($elements as $element) {
            $this->addClassToElement($element, $class, $overwrite);
        }

        return $this;
    }

    /**
     * Add a class to a specific element
     *
     * @param \DOMElement $element
     * @param string $class
     * @param bool $overwrite
     */
    private function addClassToElement(\DOMElement $element, string $class, bool $overwrite): void
    {
        if ($element->hasAttribute('class') && !$overwrite) {
            $currentClass = $element->getAttribute('class');
            $element->setAttribute('class', "{$currentClass} {$class}");
        } else {
            $element->setAttribute('class', $class);
        }
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
        $modifier = new self($html);
        return $modifier->addClassToTag($tag, $class, $overwrite)->save();
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
        $modifier = new self($html);

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
        while ($node !== null && $node->nodeType === XML_ELEMENT_NODE) {
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
     * @param string $altText The alt text to set for the found img element.
     * @param bool $override Whether to override existing alt text. Default is false.
     * @return $this
     */
    public function addAltText(string $filename, string $altText, bool $override = false): self
    {
        $images = $this->dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if ($img instanceof \DOMElement) {
                $src = $img->getAttribute('src');
                if (strpos($src, $filename) !== false) {
                    if ($override || (!$img->hasAttribute('alt') || $img->getAttribute('alt') === '')) {
                        $img->setAttribute('alt', $altText);
                    }
                }
            } else {
                throw new \InvalidArgumentException('The node is not an instance of DOMElement');
            }
        }
    
        return $this;
    }

    /**
     * Adds ruby annotations to the specified target text within the HTML document.
     *
     * @param string $target The target text to add ruby annotations to
     * @param string $rt The ruby text to be added as annotation
     * @param int $limit The maximum number of occurrences of the target text to be processed (0 means all occurrences)
     * @param bool $rb If true, wrap the target text in an <rb> element; otherwise, use a text node
     * @param bool $rp If true, add <rp>(</rp> before the <rt> element and <rp>)</rp> after the <rt> element
     * @return $this
     */
    public function addRubyText(string $target, string $rt, int $limit = 0, bool $rb = false, bool $rp = false): self
    {
        $xpath = new DOMXPath($this->dom);
        $textNodes = $xpath->query("//text()[not(ancestor::head or ancestor::ruby)]");

        $count = 0;
        $pattern = '/(' . preg_quote($target, '/') . ')/u';
        $replacement = $this->createRubyReplacement($target, $rt, $rb, $rp, $limit, $count);

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

        return $this;
    }

    /**
     * Create a replacement callback for ruby annotations
     *
     * @param string $target
     * @param string $rt
     * @param bool $rb
     * @param bool $rp
     * @param int $limit
     * @param int &$count
     * @return callable
     */
    private function createRubyReplacement(string $target, string $rt, bool $rb, bool $rp, int $limit, int &$count): callable
    {
        return function ($matches) use ($target, $rt, $rb, $rp, $limit, &$count) {
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
    }
}