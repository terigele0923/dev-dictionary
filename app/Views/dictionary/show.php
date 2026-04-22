<?php $title = '辞書詳細'; ?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1><?= e($entry['title']) ?></h1>
        <div class="inline">
            <a class="btn btn-secondary" href="/dictionary/edit?id=<?= e($entry['entry_id']) ?>">編集</a>
            <a class="btn btn-light" href="/dictionary/history?id=<?= e($entry['entry_id']) ?>">履歴</a>
        </div>
    </div>
    <p class="muted">カテゴリ: <?= e($entry['category_name']) ?> / ステータス: <?= e($entry['status']) ?> / 優先度: <?= e($entry['priority_level']) ?> / version: <?= e($entry['version_no']) ?></p>
    <div class="grid">
        <div><strong>slug</strong><div><?= e($entry['slug']) ?></div></div>
        <div><strong>問題概要</strong><div><?= nl2br(e($entry['problem_summary'])) ?></div></div>
        <div><strong>原因</strong><div><?= nl2br(e($entry['root_cause'])) ?></div></div>
        <div><strong>確認ポイント</strong><div><?= nl2br(e($entry['check_points'])) ?></div></div>
        <div><strong>コマンド例</strong><pre><?= e($entry['command_examples']) ?></pre></div>
        <div><strong>解決方法</strong><div><?= nl2br(e($entry['solution_summary'])) ?></div></div>
        <div><strong>注意点</strong><div><?= nl2br(e($entry['caution_notes'])) ?></div></div>
        <div><strong>キーワード</strong>
            <div class="inline">
                <?php foreach ($entry['keywords'] as $keyword): ?>
                    <span class="badge"><?= e($keyword['keyword']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="muted">作成日時: <?= e($entry['created_at']) ?> / 更新日時: <?= e($entry['updated_at']) ?></div>
    </div>
</div>
<div class="card inline">
    <a class="btn btn-light" href="/dictionary">一覧へ戻る</a>
    <form method="post" action="/dictionary/delete" onsubmit="return confirm('この辞書を削除しますか？');" style="margin:0;">
        <?= csrf_field() ?>
        <input type="hidden" name="entry_id" value="<?= e($entry['entry_id']) ?>">
        <button class="btn btn-danger" type="submit">削除</button>
    </form>
</div>
