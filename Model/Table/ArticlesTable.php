<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
    }

    // add/edit の両アクションにおいて、本文とタイトルは空ではならず、必要不可欠である
    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmptyString('title')
            ->requirePresence('title')
            ->notEmptyString('body')
            ->requirePresence('body');
        
        return $validator;
    }

    // articles テーブルにレコードの存在確認
    // articles.id = $articleId であるレコードの user_id と、ログインユーザの id が一致してれば true -> controller で記事の編集と削除を許可する
    public function isOwnedBy($articleId, $userId)
    {
        return $this->exists(['id' => $articleId, 'user_id' => $userId]);
    }
}