<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Denshoch\Utils;

class UtilsTest extends TestCase
{
    public function testRemoveCtrlChars()
    {
        $source = "[text]&#x0;&#x1;&#x2;&#x3;&#x4;&#x5;&#x6;&#x7;&#x8;&#x9;&#xb;&#xc;&#xe;&#xf;&#x10;&#x11;&#x12;&#x13;&#x14;&#x15;&#x16;&#x17;&#x18;&#x19;&#x1a;&#x1b;&#x1c;&#x1d;&#x1e;&#x1f;&#x7f;[/text]";
        $source = mb_convert_encoding( $source, 'UTF-8', 'HTML-ENTITIES' );
        $excpected = "[text][/text]";
        $actual = Utils::removeCtrlChars( $source );
        $this->assertEquals( $excpected, $actual );
    }

    public function testLoadXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><node>test</node></root>';
        $dom = Utils::loadXml($xml);
        $this->assertInstanceOf(DOMDocument::class, $dom);
        $this->assertEquals("test", $dom->getElementsByTagName("node")[0]->nodeValue);
    }

    /**
     * @expectedException \DOMException
     * @expectedExceptionMessage Failed to load XML
     */
    public function testLoadXmlWithInvalidXml1()
    {
        $xml = '<root><node>test</nod></root>';
        $this->expectException(\DOMException::class);
        $this->expectExceptionMessage('Opening and ending tag mismatch');
        Utils::loadXml($xml);
    }

    /**
     * @expectedException \DOMException
     * @expectedExceptionMessage Failed to load XML
     */
    public function testLoadXmlWithInvalidXml2()
    {
        $xml = 'test';
        $this->expectException(\DOMException::class);
    
        try {
            Utils::loadXml($xml);
        } catch (\DOMException $e) {
            $this->assertStringContainsString("Start tag expected, '<' not found", $e->getMessage());
            throw $e; // 再スローして例外が発生したことを確認
        }
    }

    public function testInnerXML()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <_denshoch><item id="1">Item 1</item><item id="2">Item 2</item><item id="3">Item 3</item></_denshoch>';
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);
        $node = $dom->getElementsByTagName('_denshoch')->item(0);
        
        $expectedXml = '<item id="1">Item 1</item><item id="2">Item 2</item><item id="3">Item 3</item>';
        $this->assertEquals($expectedXml, Utils::innerXML($node));
        $this->assertEquals($expectedXml, Utils::innerXML($dom->documentElement));
    }
}