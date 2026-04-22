<?php $title = '辞書一覧'; ?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1>辞書一覧</h1>
        <a class="btn" href="/dictionary/create">新規登録</a>
    </div>
    <form method="get" action="/dictionary" class="grid grid-2">
        <div>
            <label>カテゴリ</label>
            <select name="category_id">
                <option value="">すべて</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['category_id']) ?>" <?= (string)($filters['category_id'] ?? '') === (string)$category['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>ステータス</label>
            <select name="status">
                <option value="">すべて</option>
                <?php foreach (['draft' => '下書き', 'published' => '公開', 'archived' => 'アーカイブ'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>タイトル</label>
            <input type="text" name="title" value="<?= e($filters['title'] ?? '') ?>">
        </div>
        <div>
            <label>キーワード</label>
            <input type="text" name="keyword" value="<?= e($filters['keyword'] ?? '') ?>">
        </div>
        <div class="inline">
            <button class="btn" type="submit">検索</button>
            <a class="btn btn-light" href="/dictionary">リセット</a>
        </div>
    </form>
</div>
<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>カテゴリ</th><th>タイトル</th><th>ステータス</th><th>優先度</th><th>更新日時</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= e($entry['entry_id']) ?></td>
                <td><?= e($entry['category_name']) ?></td>
                <td><a href="/dictionary/show?id=<?= e($entry['entry_id']) ?>"><?= e($entry['title']) ?></a></td>
                <td><span class="badge"><?= e($entry['status']) ?></span></td>
                <td><?= e($entry['priority_level']) ?></td>
                <td><?= e($entry['updated_at']) ?></td>
                <td class="inline">
                    <a class="btn btn-light" href="/dictionary/show?id=<?= e($entry['entry_id']) ?>">詳細</a>
                    <a class="btn btn-secondary" href="/dictionary/edit?id=<?= e($entry['entry_id']) ?>">編集</a>
                    <a class="btn btn-light" href="/dictionary/history?id=<?= e($entry['entry_id']) ?>">履歴</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($entries)): ?>
            <tr><td colspan="7">データがありません。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
