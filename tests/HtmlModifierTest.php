<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Denshoch\HtmlModifier;

class HtmlModifierTest extends TestCase {

  // addClassToTagメソッドのテスト
  public function testAddClassToTag() {
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

  // addClassToTagメソッドのテスト。override
  public function testAddClassToTagAppend() {
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
  public function testAddClassToTagOverride() {
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


  // modifyメソッドのテスト
  public function testModify() {
    // 元のHTML文字列
    $html = "<div><p>Hello, world!</p></div>";

    // modifyメソッドを呼び出し、結果を変数に保存する
    $result = HtmlModifier::modify($html, 'p', 'my-class');

    // 期待するHTML文字列
    $expected = '<div><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }

  // modifyMultipleメソッドのテスト
  public function testModifyMultiple() {
    // 元のHTML文字列
    $html = "<div><p>Hello, world!</p></div>";

    // タグ名とクラス名のペア
    $tagClassPairs = [
      'p' => 'my-class',
      'div' => 'my-other-class'
    ];

    // modifyMultipleメソッドを呼び出し、結果を変数に保存する
    $result = HtmlModifier::modifyMultiple($html, $tagClassPairs);

    $expected = '<div class="my-other-class"><p class="my-class">Hello, world!</p></div>';

    // 結果と期待値を比較する
    $this->assertEquals($expected, $result);
  }
}