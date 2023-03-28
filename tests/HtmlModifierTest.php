<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Denshoch\HtmlModifier;

class HtmlModifierTest extends TestCase
{

  // addClassToTagメソッドのテスト
  public function testAddClassToTag()
  {
    // 元のHTML文字列
    $html = "<div><p>Hello, world!</p></div>";

    // HtmlModifierクラスのインスタンスを作成する
    $modifier = new HtmlModifier($html);

    // addClassToTagメソッドを呼び出し、結果を変数に保存する
    $result = $modifier->addClassToTag('p', 'my-class')->save();

    // 期待するHTML文字列
    $expected = '<div><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  // addClassToTagメソッドのテスト
  public function testAddClassToTagRootless()
  {
    // 元のHTML文字列
    $html = "test<p>Hello, world!</p>test";

    // HtmlModifierクラスのインスタンスを作成する
    $modifier = new HtmlModifier($html);

    // addClassToTagメソッドを呼び出し、結果を変数に保存する
    $result = $modifier->addClassToTag('p', 'my-class')->save();

    // 期待するHTML文字列
    $expected = 'test<p class="my-class">Hello, world!</p>test';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  // addClassToTagメソッドのテスト。override
  public function testAddClassToTagAppend()
  {
    // 元のHTML文字列
    $html = '<div><p class="prev">Hello, world!</p></div>';

    // HtmlModifierクラスのインスタンスを作成する
    $modifier = new HtmlModifier($html);

    // addClassToTagメソッドを呼び出し、結果を変数に保存する
    $result = $modifier->addClassToTag('p', 'my-class')->save();

    // 期待するHTML文字列
    $expected = '<div><p class="prev my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  // addClassToTagメソッドのテスト。override
  public function testAddClassToTagOverride()
  {
    // 元のHTML文字列
    $html = '<div><p class="prev">Hello, world!</p></div>';

    // HtmlModifierクラスのインスタンスを作成する
    $modifier = new HtmlModifier($html);

    // addClassToTagメソッドを呼び出し、結果を変数に保存する
    $result = $modifier->addClassToTag('p', 'my-class', true)->save();

    // 期待するHTML文字列
    $expected = '<div><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }


  // addClassメソッドのテスト
  public function testaddClass()
  {
    // 元のHTML文字列
    $html = "<div><p>Hello, world!</p></div>";

    // addClassメソッドを呼び出し、結果を変数に保存する
    $result = HtmlModifier::addClass($html, 'p', 'my-class');

    // 期待するHTML文字列
    $expected = '<div><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  // addClassMultipleメソッドのテスト
  public function testAddClassMultiple()
  {
    // 元のHTML文字列
    $html = "<div><p>Hello, world!</p></div>";

    // タグ名とクラス名のペア
    $tagClassPairs = [
      'p' => 'my-class',
      'div' => 'my-other-class'
    ];

    // addClassMultipleメソッドを呼び出し、結果を変数に保存する
    $result = HtmlModifier::addClassMultiple($html, $tagClassPairs);

    $expected = '<div class="my-other-class"><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  public function testIsDescendantOfTag()
  {
      $html = '<div><p>こんにちは、<ruby>太郎<rt>タロウ</rt></ruby>さん。</p></div>';
      $modifier = new HtmlModifier($html);
      $xpath = new DOMXPath($modifier->getDOM());
  
      // Use reflection to access the private isDescendantOfTag method.
      $reflection = new \ReflectionClass(HtmlModifier::class);
      $method = $reflection->getMethod('isDescendantOfTag');
      $method->setAccessible(true);
  
      // Find the '太郎' text node.
      $textNode = $xpath->query("//text()[contains(., '太郎')]")->item(0);
      $this->assertTrue($method->invoke($modifier, $textNode, 'ruby'));
  
      // Find the 'こんにちは' text node.
      $textNode = $xpath->query("//text()[contains(., 'こんにちは')]")->item(0);
      $this->assertFalse($method->invoke($modifier, $textNode, 'ruby'));
  }

  public function testAddRubyText()
  {
    $html = '<p>こんにちは、私の名前は太郎です。太郎は学生です。</p>';
    $expected = '<p>こんにちは、私の名前は<ruby>太郎<rt>タロウ</rt></ruby>です。<ruby>太郎<rt>タロウ</rt></ruby>は学生です。</p>';

    $htmlModifier = new HtmlModifier($html);
    $htmlModifier->addRubyText('太郎', 'タロウ');
    $result = $htmlModifier->save();

    $this->assertSame($expected, $result);

    $html = '<p>こんにちは、私の名前は太郎です。太郎は学生です。</p>';
    $expected = '<p>こんにちは、私の名前は<ruby><rb>太郎</rb><rt>タロウ</rt></ruby>です。太郎は学生です。</p>';

    $htmlModifier = new HtmlModifier($html);
    $htmlModifier->addRubyText('太郎', 'タロウ', 1, true);
    $result = $htmlModifier->save();

    $this->assertSame($expected, $result);

    $html = '<p>こんにちは、私の名前は太郎です。太郎は学生です。</p>';
    $expected = '<p>こんにちは、私の名前は<ruby>太郎<rp>(</rp><rt>タロウ</rt><rp>)</rp></ruby>です。<ruby>太郎<rp>(</rp><rt>タロウ</rt><rp>)</rp></ruby>は学生です。</p>';

    $htmlModifier = new HtmlModifier($html);
    $htmlModifier->addRubyText('太郎', 'タロウ', 0, false, true);
    $result = $htmlModifier->save();

    $this->assertSame($expected, $result);
  }
}
