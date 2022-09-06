<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use Cake\Log\Log;

class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        // belongsToManyは多対多のリレーション
        // ここはArticlesテーブルなので、記事とタグが多対多で紐づくことを規定している
        $this->belongsToMany('Tags');
    }

    // 保存前に通る処理
    public function beforeSave($event, $entity, $options)
    {
        if ($entity->tag_string) {
            $entity->tags = $this->_buildTags($entity->tag_string);
        }

        Log::debug($entity->isNew());

        // // レコードが既に存在する、かつslugカラムの値が入ってないとき
        // if ($entity->isNew() && !$entity->slug) {
        //     $sluggedTitle = Text::slug($entity->title);
        //     // スラグをスキーマで定義されている最大長に調整
        //     // 生成したスラグ文字列の先頭から最大191バイトまでを取得し、slugとする
        //     $entity->slug = substr($sluggedTitle, 0, 191);
        // }
    }

    public function validationDefault(Validator $validator)
    {
        // titleとbodyについてvalidationを設定
        $validator
            // allowEmpty = false なので空フィールドを許容しない
            ->allowEmptyString('title', false)
            ->minLength('title', 10)
            ->maxLength('title', 255)

            ->allowEmptyString('body', false)
            ->minLength('body', 10);

        return $validator;
    }

    // カスタムファインダーメソッドfindTagged()を実装する
    // $queryはクエリービルダーのインスタンス
    // $optionsにはArticlesControllerのtags()でfind('tagged')に渡した"tags"オプションが含まれている
    public function findTagged(Query $query, array $options)
    {
        $columns = [
            'Articles.id', 'Articles.user_id', 'Articles.title',
            'Articles.body', 'Articles.published', 'Articles.created',
            'Articles.slug',
        ];

        $query = $query
            -> select($columns)
            -> distinct($columns);

        if (empty($options['tags'])) {
            // タグが指定されていない場合は、タグのない記事を検索する
            // nullを条件に指定してるからleftjoinなんだね〜
            $query -> leftJoinWith('Tags')
                -> where(['Tags.title IS' => null]);
        } else {
            // 提供されたタグが1つ以上ある記事を検索する
            $query -> innerJoinWith('Tags')
                -> where(['Tags.title IN' => $options['tags']]);
        }

        // Articlesテーブルのidでグループ化して返す
        return $query->group(['Articles.id']);
    }

    protected function _buildTags($tagString)
    {
        // タグのトリミング
        $newTags = array_map('trim', explode(',', $tagString));
        // すべての空のタグを削除
        $newTags = array_filter($newTags);
        // 重複するタグを削除
        $newTags = array_unique($newTags);

        // 空リストを準備
        $out = [];
        $query = $this->Tags->find()
            ->where(['Tags.title IN' => $newTags]);
        
        // newTagsリストの中に既存のタグが存在する場合、既存タグを削除
        foreach ($query->extract('title') as $existing) {
            $index = array_search($existing, $newTags);
            if ($index !== false) {
                unset($newTags[$index]);
            }
        }

        // 既存のタグの追加
        foreach ($query as $tag) {
            $out[] = $tag;
        }

        // 新しいタグの追加
        foreach ($newTags as $tag) {
            $out[] = $this->Tags->newEntity(['title' => $tag]);
        }

        return $out;
    }
}

