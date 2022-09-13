<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function validationDefault(Validator $validator)
    {
        return $validator
            ->notEmptyString('username', 'A username is required.')
            ->notEmptyString('password', 'A password is required.')
            ->notEmptyString('role', 'A role is required.')
            // role について追加のチェック項目を設定
            // 入力値が admin/author だったらOK
            ->add('role', 'inList', [
                'rule' => ['inList', ['admin', 'author']],
                'message' => 'Please enter a valid role.'
            ]);
    }
}