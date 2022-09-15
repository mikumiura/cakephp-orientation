<?php
namespace App\Controller;

use Cake\Http\Exception\NotFoundException;

class ArticlesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function index()
    {
        // $articles = $this->Articles->find('all');
        // $this->set(compact('articles'));
        $this->set('articles', $this->Articles->find('all'));
    }

    public function view($id = null)
    {
        // $id はリクエストされたURLを通して渡される
        $article = $this->Articles->get($id);
        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            // Auth コンポーネントがセッションファイル（リクエスト情報が保存されてる）から取ってきたログインユーザの id で $article の user_id カラムを更新
            $article->user_id = $this->Auth->user('id');
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $this->set('article', $article);

        // ツリーリストの形で categories を取得
        // articles/add の方だよ、、
        $categories = $this->Articles->Categories->find('treeList');
        $this->set(compact('categories'));
    }

    public function edit($id = null)
    {
        $article = $this->Articles->get($id);
        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }
        $this->set('article', $article);
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article with id: {0} has been deleted.', h($id)));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function isAuthorized($user)
    {
        // $this->log($user, 'debug');

        // 登録ユーザは全員記事を追加できる
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

        // その記事の所有者であれば記事を編集できる
        if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {
            $articleId = (int)$this->request->getParam('pass.0');
            if ($this->Articles->isOwnedBy($articleId, $user['id'])) { // $user['id'] はセッションファイルに保存されてるログインユーザの id
                return true;
            }
        }

        return parent::isAuthorized($user);
    }
}