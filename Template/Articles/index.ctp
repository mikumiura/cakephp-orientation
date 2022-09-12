<h1>ブログ記事一覧</h1>
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Created</th>
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
    </tr>
    <?php endforeach; ?>
</table>