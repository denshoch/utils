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

    // removeTagメソッドをテストする
    public function testRemoveTag()
    {
        // HTML文字列
        $html = '<p>This is a <ruby><rb>text</rb><rp>（</rp><rt>ruby</rt><rp>）</rp></ruby>.</p>';

        // HtmlModifierクラスのインスタンスを作成する
        $modifier = new HtmlModifier($html);

        // removeTagメソッドを呼び出し、引数に'trong'を渡す
        // これにより、HTML文字列内のすべての'strong'タグが削除される
        $result = $modifier->removeTag('rp');

        // 期待される結果のHTML文字列
        $expected = '<p>This is a <ruby><rb>text</rb><rt>ruby</rt></ruby>.</p>';

        // removeTagメソッドが期待される結果を返したかどうかを確認する
        $this->assertEquals($expected, $result->save());
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
}