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
        Log::debug("_getTagString");

        // // save前レコードにtag_stringがある場合はこのifでreturnしてる！
        // if (isset($this->_properties['tag_string'])) {
        //     // Log::debug("ここです！");
        //     return $this->_properties['tag_string'];
        // }
        // // tagsが生成されなかった時の処理
        // if (empty($this->tags)) {
        //     Log::debug("からですか");
        //     return '';
        // }

        // tag_stringがunsetでtagsがsetされてる時
        $tags = new Collection($this->tags);
        $str = $tags->reduce(function ($string, $tag) {
            Log::debug('通って欲しい処理');
            return $string . $tag->title . ', ';
        }, '');

        // 最後尾の要素にも', 'をつけているのでtrim()で除去する
        return trim($str, ', ');
    }
}