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

    protected function _getTagString()
    {
        // Log::debug("_getTagString");

        // 記事の内容を add/edit したとき
        // save前レコードにtag_stringがある場合はこのifでreturnしてる
        if (isset($this->_properties['tag_string'])) {
            return $this->_properties['tag_string'];
        }

        // add ページの読み込み時
        if (empty($this->tags)) {
            return '';
        }

        // edit ページの読み込み時
        if ($this->tags) {
            $tags = new Collection($this->tags);
            Log::debug($tags);
            
            $str = $tags->reduce(function ($string, $tag) {
                return $string . $tag->title . ', ';
            }, '');

            // 最後尾の要素にも', 'をつけているのでtrim()で除去する
            return trim($str, ', ');
        }
        
    }
}