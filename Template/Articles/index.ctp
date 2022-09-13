<h1>ブログ記事一覧</h1>
<?= $this->Html->link('記事の追加', ['action' => 'add']) ?>
<table>
    <tr>
        <th>Id</th>
        <th>Title</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php foreach ($articles as $article): ?>
    <tr>
        <td><?= $article->id ?></td>
        <td>
            <!-- "/view/$articles->id" に遷移するリンクを作成 -->
            <?= $this->Html->link($article->title, ['action' => 'view', $article->id]) ?>
        </td>
        <td>
            <?= $article->created->format(DATE_RFC850) ?>
        </td>
        <td>
            <?=
                $this->Form->postlink(
                    '削除',
                    ['action' => 'delete', $article->id],
                    ['confirm' => 'Are you sure?'])
            ?>
            <?= $this->Html->link('編集', ['action' => 'edit', $article->id]) ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>