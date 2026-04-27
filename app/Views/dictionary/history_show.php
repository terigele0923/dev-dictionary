<?php $title = '履歴詳細'; ?>
<div class="card">
    <h1>履歴詳細 v<?= e($history['version_no']) ?></h1>
    <p class="muted">カテゴリ: <?= e($history['category_name']) ?> / メモタイプ: <?= e($history['memo_type_name'] ?? '-') ?> / 保存日時: <?= e($history['snapshot_created_at']) ?></p>
    <?php if (($history['memo_type_display_mode'] ?? 'section') === 'table'): ?>
        <?php $rows = $history['field_snapshot_rows'] ?: [1 => $history['field_snapshots']]; ?>
        <?php $headerColumns = $rows ? array_values($rows)[0] : $history['field_snapshots']; ?>
        <div class="card" style="padding:0; overflow:auto; box-shadow:none; border:1px solid #e5e7eb;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>タイトル</th>
                        <?php foreach ($headerColumns as $field): ?>
                            <th><?= e($field['label'] ?? '') ?></th>
                        <?php endforeach; ?>
                        <th>キーワード</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $rowNo => $fields): ?>
                        <tr>
                            <td><?= e((string) $rowNo) ?></td>
                            <td style="min-width:180px;"><?= e($history['title']) ?></td>
                            <?php foreach ($fields as $field): ?>
                                <td style="white-space:pre-wrap; min-width:180px;"><?= e($field['value'] ?? '') ?></td>
                            <?php endforeach; ?>
                            <td style="min-width:180px;"><?= (int) $rowNo === 1 ? e($history['keyword_snapshot']) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="grid">
            <div><strong>タイトル</strong><div><?= e($history['title']) ?></div></div>
            <?php foreach ($history['field_snapshots'] as $field): ?>
                <div>
                    <strong><?= e($field['label'] ?? '') ?></strong>
                    <div><?= nl2br(e($field['value'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
            <div><strong>キーワード</strong><div><?= e($history['keyword_snapshot']) ?></div></div>
        </div>
    <?php endif; ?>
</div>
<div class="card">
    <a class="btn btn-light" href="/dictionary/history?id=<?= e($history['entry_id']) ?>">履歴一覧へ戻る</a>
</div>
