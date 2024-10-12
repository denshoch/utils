<?php

use PHPUnit\Framework\TestCase;
use Denshoch\HtmlModifier;

class HtmlModifierTest extends TestCase
{
    public function testAddClassInvalidXML()
    {
        $html = "<div><p>Test</p><p>Another Test</p>";
        $this->expectException(InvalidArgumentException::class);
        //$this->expectExceptionMessage("Start tag expected, '<' not found in Entity");
        $result = HtmlModifier::addClass($html, 'p', 'new-class');
    }

    public function testAddClass()
    {
        $html = '<div><p>Test</p><p>Another Test</p></div>';
        $expected = '<div><p class="new-class">Test</p><p class="new-class">Another Test</p></div>';
        $result = HtmlModifier::addClass($html, 'p', 'new-class');
        $this->assertEquals($expected, $result);
    }

    public function testAddClassWithOverwrite()
    {
        $html = '<div><p class="old-class">Test</p></div>';
        $expected = '<div><p class="new-class">Test</p></div>';
        $result = HtmlModifier::addClass($html, 'p', 'new-class', true);
        $this->assertEquals($expected, $result);
    }

    public function testAddClassMultiple()
    {
        $html = '<div><p>Test</p><span>Another Test</span></div>';
        $expected = '<div><p class="p-class">Test</p><span class="span-class">Another Test</span></div>';
        $tagClassPairs = ['p' => 'p-class', 'span' => 'span-class'];
        $result = HtmlModifier::addClassMultiple($html, $tagClassPairs);
        $this->assertEquals($expected, $result);
    }

    // addClassToTagメソッドのテスト
    public function testAddClassRootless()
    {
        // 元のHTML文字列
        $html = "test<p>Hello, world!</p>test";

        $result = HtmlModifier::addClass($html, 'p', 'my-class');

        // 期待するHTML文字列
        $expected = 'test<p class="my-class">Hello, world!</p>test';

        // 結果と期待値を比較する
        $this->assertEquals($expected, $result);
    }

    public function testAddAltText()
    {
        $html = '<div><img src="image.jpg"/></div>';
        $expected = '<div><img src="image.jpg" alt="Description"/></div>';
        $modifier = new HtmlModifier($html);
        $result = $modifier->addAltText('image.jpg', 'Description')->save();
        $this->assertEquals($expected, $result);
    }

    public function testAddAltTextWithOverride()
    {
        $html = '<div><img src="image.jpg" alt="Old Description"/></div>';
        $expected = '<div><img src="image.jpg" alt="New Description"/></div>';
        $modifier = new HtmlModifier($html);
        $result = $modifier->addAltText('image.jpg', 'New Description', true)->save();
        $this->assertEquals($expected, $result);
    }

    public function testAddRubyText()
    {
        $html = '<div>Test text with ruby annotation</div>';
        $expected = '<div>Test text with <ruby>ruby<rt>annotation</rt></ruby> annotation</div>';
        $modifier = new HtmlModifier($html);
        $result = $modifier->addRubyText('ruby', 'annotation')->save();
        $this->assertEquals($expected, $result);
    }

    public function testAddRubyTextWithLimit()
    {
        $html = '<div>ruby ruby ruby</div>';
        $expected = '<div><ruby>ruby<rt>annotation</rt></ruby> ruby ruby</div>';
        $modifier = new HtmlModifier($html);
        $result = $modifier->addRubyText('ruby', 'annotation', 1)->save();
        $this->assertEquals($expected, $result);
    }

    public function testAddRubyTextWithRbAndRp()
    {
        $html = '<div>ruby</div>';
        $expected = '<div><ruby><rb>ruby</rb><rp>(</rp><rt>annotation</rt><rp>)</rp></ruby></div>';
        $modifier = new HtmlModifier($html);
        $result = $modifier->addRubyText('ruby', 'annotation', 0, true, true)->save();
        $this->assertEquals($expected, $result);
    }

    public function testSaveAsXHTML5()
    {
        $html = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>title</title></head><body><p>Hello world!</p></body></html>';
        $expected = $html;

        $htmlModifier = new HtmlModifier($html);
        $result = $htmlModifier->save();

        $this->assertXmlStringEqualsXmlString($expected, $result);
    }

    public function testXMLNamespace()
    {
        $html = '<p epub:type="para">こんにちは、私の名前は太郎です。太郎は学生です。</p>';
        $expected = $html;

        $htmlModifier = new HtmlModifier($html);
        $result = $htmlModifier->save();


        $this->assertXmlStringEqualsXmlString($expected, $result);
    }
}
