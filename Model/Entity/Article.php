<?php
// src/Model/Entity/Article.php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Collection\Collection;
use Cake\Log\Log;

class Article extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'slug' => false,
    ];

    // tag_stringをDBから読み出す際の加工処理
    protected function _getTagString()
    {
        // Log::debug("_getTagString");

        // 基本的に記事追加した時はここを通る想定
        // save前レコードにtag_stringがある場合はこのifでreturnしてる
        if (isset($this->_properties['tag_string'])) {
            return $this->_properties['tag_string'];
        }

        // 記事の追加ページ読み込むとき
        // tagsが生成されなかった時の処理
        Log::debug($this->tags);
        if (empty($this->tags)) {
            return '';
        }

        // tag_stringは""で、tagsは生成されてる時
        Log::debug("hoge");
        if ($this->tags) {
            $tags = new Collection($this->tags);
            $str = $tags->reduce(function ($string, $tag) {
                Log::debug('通って欲しい処理');
                return $string . $tag->title . ', ';
            }, '');

            // 最後尾の要素にも', 'をつけているのでtrim()で除去する
            return trim($str, ', ');
        }
        
    }
}