<?php $title = 'カテゴリ'; ?>
<div class="grid grid-2">
    <div class="card">
        <h1>カテゴリ一覧</h1>
        <table>
            <thead><tr><th>ID</th><th>カテゴリ名</th><th>説明</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['category_id']) ?></td>
                    <td><?= e($category['category_name']) ?></td>
                    <td><?= e($category['description']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2>カテゴリ追加</h2>
        <form method="post" action="/categories/store" class="grid">
            <?= csrf_field() ?>
            <div>
                <label>カテゴリ名</label>
                <input type="text" name="category_name">
            </div>
            <div>
                <label>説明</label>
                <input type="text" name="description">
            </div>
            <button class="btn" type="submit">追加</button>
        </form>
    </div>
</div>
