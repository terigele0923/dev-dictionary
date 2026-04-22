<?php $title = '履歴一覧'; ?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1>履歴一覧</h1>
        <a class="btn btn-light" href="/dictionary/show?id=<?= e($entryId) ?>">詳細へ戻る</a>
    </div>
    <table>
        <thead><tr><th>version</th><th>カテゴリ</th><th>タイトル</th><th>ステータス</th><th>保存日時</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($histories as $history): ?>
            <tr>
                <td><?= e($history['version_no']) ?></td>
                <td><?= e($history['category_name']) ?></td>
                <td><?= e($history['title']) ?></td>
                <td><?= e($history['status']) ?></td>
                <td><?= e($history['snapshot_created_at']) ?></td>
                <td><a class="btn btn-light" href="/dictionary/history/show?history_id=<?= e($history['history_id']) ?>">表示</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($histories)): ?><tr><td colspan="6">履歴はありません。</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
