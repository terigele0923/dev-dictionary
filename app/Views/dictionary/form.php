<?php $title = $mode === 'create' ? '辞書登録' : '辞書編集'; ?>
<div class="card">
    <h1><?= e($title) ?></h1>
    <form method="post" action="<?= $mode === 'create' ? '/dictionary/store' : '/dictionary/update' ?>" class="grid">
        <?= csrf_field() ?>
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="entry_id" value="<?= e($entry['entry_id']) ?>">
        <?php endif; ?>
        <div class="grid grid-2">
            <div>
                <label>カテゴリ</label>
                <select name="category_id">
                    <option value="">選択してください</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['category_id']) ?>" <?= (string)($entry['category_id'] ?? '') === (string)$category['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>タイトル</label>
                <input type="text" name="title" value="<?= e($entry['title'] ?? '') ?>">
            </div>
            <div>
                <label>slug</label>
                <input type="text" name="slug" value="<?= e($entry['slug'] ?? '') ?>">
            </div>
            <div>
                <label>ステータス</label>
                <select name="status">
                    <?php foreach (['draft' => '下書き', 'published' => '公開', 'archived' => 'アーカイブ'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($entry['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>優先度 (1〜5)</label>
                <input type="number" name="priority_level" min="1" max="5" value="<?= e((string)($entry['priority_level'] ?? 3)) ?>">
            </div>
            <div>
                <label>キーワード（カンマ区切り）</label>
                <input type="text" name="keywords" value="<?= e($keywordsText ?? '') ?>">
            </div>
        </div>
        <div><label>問題概要</label><textarea name="problem_summary"><?= e($entry['problem_summary'] ?? '') ?></textarea></div>
        <div><label>原因</label><textarea name="root_cause"><?= e($entry['root_cause'] ?? '') ?></textarea></div>
        <div><label>確認ポイント</label><textarea name="check_points"><?= e($entry['check_points'] ?? '') ?></textarea></div>
        <div><label>コマンド例</label><textarea name="command_examples"><?= e($entry['command_examples'] ?? '') ?></textarea></div>
        <div><label>解決方法</label><textarea name="solution_summary"><?= e($entry['solution_summary'] ?? '') ?></textarea></div>
        <div><label>注意点</label><textarea name="caution_notes"><?= e($entry['caution_notes'] ?? '') ?></textarea></div>
        <div class="inline">
            <button class="btn" type="submit"><?= $mode === 'create' ? '登録する' : '更新する' ?></button>
            <a class="btn btn-light" href="/dictionary">戻る</a>
        </div>
    </form>
</div>
