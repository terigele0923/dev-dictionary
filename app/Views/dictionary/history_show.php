<?php $title = '履歴詳細'; ?>
<div class="card">
    <h1>履歴詳細 v<?= e($history['version_no']) ?></h1>
    <p class="muted">カテゴリ: <?= e($history['category_name']) ?> / 保存日時: <?= e($history['snapshot_created_at']) ?></p>
    <div class="grid">
        <div><strong>タイトル</strong><div><?= e($history['title']) ?></div></div>
        <div><strong>slug</strong><div><?= e($history['slug']) ?></div></div>
        <div><strong>問題概要</strong><div><?= nl2br(e($history['problem_summary'])) ?></div></div>
        <div><strong>原因</strong><div><?= nl2br(e($history['root_cause'])) ?></div></div>
        <div><strong>確認ポイント</strong><div><?= nl2br(e($history['check_points'])) ?></div></div>
        <div><strong>コマンド例</strong><pre><?= e($history['command_examples']) ?></pre></div>
        <div><strong>解決方法</strong><div><?= nl2br(e($history['solution_summary'])) ?></div></div>
        <div><strong>注意点</strong><div><?= nl2br(e($history['caution_notes'])) ?></div></div>
        <div><strong>キーワード</strong><div><?= e($history['keyword_snapshot']) ?></div></div>
    </div>
</div>
<div class="card">
    <a class="btn btn-light" href="/dictionary/history?id=<?= e($history['entry_id']) ?>">履歴一覧へ戻る</a>
</div>
