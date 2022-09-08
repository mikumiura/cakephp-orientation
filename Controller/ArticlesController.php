<?php
// src/Controller/ArticlesController.php

namespace App\Controller;

// "AppController"で呼び出せるように
use App\Controller\AppController;

class ArticlesController extends AppController
{

    public function initialize()
    {
        // 親クラス（AppController）のinitialize()メソッドを呼び出す
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');

        $this->Auth->allow(['tags']);
    }

    // /articles/indexにアクセスが来た時に処理される
    public function index()
    {
        $articles = $this->Paginator->paginate($this->Articles->find());
        // $this->log($articles, 'debug');
        $this->set(compact('articles'));
    }

    // /articles/viewにアクセスが来た時に処理される
    public function view($slug = null)
    {
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->set(compact('article'));
    }

    // 記事の追加
    public function add()
    {
        // articlesテーブルに新規レコードを一件追加
        $article = $this->Articles->newEntity();

        if ($this->request->is('post')) {

            // add画面で入力したデータ（postデータ）をgetData()で取得し、作成した新規レコードを上書きする　※まだ保存はしない
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            
            // $article->user_id = 1;
            $article->user_id = $this->Auth->user('id'); // authコンポーネントがサーバのセッションファイルからuserのidを読み出してる

            $this->log('before save', 'debug');
            // $this->log($article, 'debug');
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                $this->log('after save', 'debug');
                // 同じcontroller内のindexに遷移する（リダイレクト）
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }

        // タグのリストを取得
        $tags = $this->Articles->Tags->find('list');
        $this->set('tags', $tags);
        
        $this->set('article', $article);
    }

    // 記事の編集
    public function edit($slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags') // 関連づけられたTagsを読み込む
            ->firstOrFail();
        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData(), [
                'accessibleFields' => ['user_id' => false] // user_idはセッションから一意に決まるので、編集できないようにする
            ]);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }
        // タグのリストを取得
        $tags = $this->Articles->Tags->find('list');
        $this->set('tags', $tags);

        $this->set('article', $article);
    }

    // 記事の削除
    public function delete($slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
            return $this->redirect(['action' => 'index']);
        }
    }

    // タグの取得
    public function tags()
    {
        // /tagsにアクセスが来たとき、/tags以下のパスパラメータを取得して、それをタグと判断する
        // /tags/hoge/fugaならhoge,fugaを$tagsに格納
        $tags = $this->request->getParam('pass');

        // $tagsタグが付いている記事を検索
        // findTaggedメソッドが存在せず怒られるので、カスタムファインダーメソッドを実装する（ArticlesTableにて）
        $articles = $this->Articles->find('tagged', [
            'tags' => $tags
        ]);

        $this->set([
            'articles' => $articles,
            'tags' => $tags
        ]);
    }

    public function isAuthorized($user)
    {
        // アクセスURLの/articles以下のパスパラメータ（＝アクション）がadd/tagsなら
        // add,tagsアクションは常にログインしているユーザに許可される
        $action = $this->request->getParam('action');
        $this->log($action, 'debug');
        if (in_array($action, ['add', 'tags'])) {
            return true;
        }

        // add/tags以外のアクション（editとdelete）にはスラグが必要ですよの処理
        // editとdeleteアクションには作業対象の記事が必要（これがスラグ）
        $slug = $this->request->getParam('pass.0');
        $this->log($slug, 'debug');
        if (!$slug) {
            return false;
        }

        // edit/deleteの後のスラグを元にarticlesテーブルからレコードを取得し、
        // user_idカラムの値とログインユーザのidが一緒ならtrueをリターンする
        $article = $this->Articles->findBySlug($slug)->first();
        $this->log($user, 'debug');
        return $article->user_id === $user['id'];

    }
}
