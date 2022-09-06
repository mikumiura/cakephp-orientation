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
            // postデータをgetData()で取得し、作成済みのレコードにセットする
            $this->log('before patchEntity', 'debug');
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $this->log('after patchEntity', 'debug');

            // $this->log($this->request->getData(), 'debug');
            
            // user_idの決め打ちは一時的なもので、あとで認証を構築する際に削除される
            $article->user_id = 1;

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
            // $article->hogehoge = 1;
            $this->Articles->patchEntity($article, $this->request->getData());
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
}
