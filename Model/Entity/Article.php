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

        // 記事の内容をadd/editしたとき
        // save前レコードにtag_stringがある場合はこのifでreturnしてる
        if (isset($this->_properties['tag_string'])) {
            return $this->_properties['tag_string'];
        }

        // addページ読み込むとき
        // tagsが生成されなかった時の処理
        if (empty($this->tags)) {
            return '';
        }

        // editページ読み込むとき
        if ($this->tags) {
            $tags = new Collection($this->tags);
            Log::debug($tags);
            
            $str = $tags->reduce(function ($string, $tag) {
                Log::debug('通って欲しい処理');
                Log::debug($string . $tag->title . ', ');
                return $string . $tag->title . ', ';
            }, '');

            // 最後尾の要素にも', 'をつけているのでtrim()で除去する
            return trim($str, ', ');
        }
        
    }
}