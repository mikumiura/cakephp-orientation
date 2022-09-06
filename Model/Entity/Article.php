<?php
// src/Model/Entity/Article.php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Collection\Collection;

class Article extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'slug' => false,
    ];

    // tag_stringをDBから読み出すた際の加工処理
    protected function _getTagString()
    {
        // entity（レコード）にtag_stringが設定されているか確認->trueならtag_stringを取得？
        if (isset($this->_properties['tag_string'])) {
            return $this->_properties['tag_string'];
        }
        if (empty($this->tags)) {
            return '';
        }

        // タグの配列を引数で渡してCollectionクラスのインスタンスを生成することで、タグ配列の操作を可能にしてる
        $tags = new Collection($this->tags);
        $str = $tags->reduce(function ($string, $tag) {
            return $string . $tag->title . ', ';
        }, '');

        // 最後尾の要素にも', 'をつけているのでtrim()で除去する
        return trim($str, ', ');
    }
}