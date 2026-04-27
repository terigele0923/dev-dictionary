<?php
$isEditing = !empty($editingCategory);
$title = $isEditing ? 'カテゴリ編集' : 'カテゴリ追加';
?>
<div class="grid grid-2">
    <div class="card">
        <h1>カテゴリ一覧</h1>
        <table>
            <thead><tr><th>ID</th><th>カテゴリ名</th><th>説明</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['category_id']) ?></td>
                    <td><?= e($category['category_name']) ?></td>
                    <td><?= e($category['description']) ?></td>
                    <td class="inline">
                        <a class="btn btn-secondary" href="/categories?edit=<?= e((string) $category['category_id']) ?>">編集</a>
                        <form method="post" action="/categories/delete" onsubmit="return confirm('このカテゴリを削除しますか？');" style="margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="category_id" value="<?= e((string) $category['category_id']) ?>">
                            <button class="btn btn-danger" type="submit">削除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr><td colspan="4">カテゴリはまだありません。</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="inline" style="justify-content:space-between;">
            <h2><?= e($title) ?></h2>
            <?php if ($isEditing): ?>
                <a class="btn btn-light" href="/categories">新規追加に戻る</a>
            <?php endif; ?>
        </div>
        <form method="post" action="<?= $isEditing ? '/categories/update' : '/categories/store' ?>" class="grid">
            <?= csrf_field() ?>
            <?php if ($isEditing): ?>
                <input type="hidden" name="category_id" value="<?= e((string) $editingCategory['category_id']) ?>">
            <?php endif; ?>
            <div>
                <label>カテゴリ名</label>
                <input type="text" name="category_name" value="<?= e($editingCategory['category_name'] ?? '') ?>">
            </div>
            <div>
                <label>説明</label>
                <input type="text" name="description" value="<?= e($editingCategory['description'] ?? '') ?>">
            </div>
            <button class="btn" type="submit"><?= $isEditing ? '更新する' : '追加する' ?></button>
        </form>
    </div>
</div>
