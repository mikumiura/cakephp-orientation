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
            ->allowEmptyString('title', false)
            ->requirePresence('title')
            ->allowEmptyString('body', false)
            ->requirePresence('body');
        
        return $validator;
    }
}