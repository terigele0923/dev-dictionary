<?php $title = '辞書詳細'; ?>
<div class="card">
    <div class="detail-hero">
        <div>
            <h1 class="detail-title"><?= e($entry['title']) ?></h1>
            <div class="inline">
                <span class="badge"><?= e($entry['memo_type_name'] ?? '-') ?></span>
                <span class="badge"><?= e($entry['status']) ?></span>
            </div>
        </div>
        <div class="inline">
            <a class="btn btn-secondary" href="/dictionary/edit?id=<?= e($entry['entry_id']) ?>">編集</a>
            <a class="btn btn-light" href="/dictionary/history?id=<?= e($entry['entry_id']) ?>">履歴</a>
        </div>
    </div>

    <div class="detail-meta">
        <div class="detail-meta-item">
            <span class="detail-meta-label">カテゴリ</span>
            <div class="detail-meta-value"><?= e($entry['category_name']) ?></div>
        </div>
        <div class="detail-meta-item">
            <span class="detail-meta-label">優先度</span>
            <div class="detail-meta-value"><?= e($entry['priority_level']) ?></div>
        </div>
        <div class="detail-meta-item">
            <span class="detail-meta-label">バージョン</span>
            <div class="detail-meta-value"><?= e($entry['version_no']) ?></div>
        </div>
        <div class="detail-meta-item">
            <span class="detail-meta-label">作成日時</span>
            <div class="detail-meta-value"><?= e($entry['created_at']) ?></div>
        </div>
        <div class="detail-meta-item">
            <span class="detail-meta-label">更新日時</span>
            <div class="detail-meta-value"><?= e($entry['updated_at']) ?></div>
        </div>
    </div>

    <?php if (($entry['memo_type_display_mode'] ?? 'section') === 'table'): ?>
        <?php $rows = $entry['field_rows'] ?: [['row_no' => 1, 'columns' => $entry['field_values']]]; ?>
        <?php $headerColumns = $rows ? array_values($rows)[0]['columns'] : $entry['field_values']; ?>
        <div class="card" style="padding:0; overflow:auto; box-shadow:none; border:1px solid #e5e7eb;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <?php foreach ($headerColumns as $field): ?>
                            <th><?= e($field['label']) ?></th>
                        <?php endforeach; ?>
                        <th>キーワード</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr>
                            <td><?= e((string) ($row['row_no'] ?? ($index + 1))) ?></td>
                            <?php foreach ($row['columns'] as $field): ?>
                                <td style="white-space:pre-wrap; min-width:180px;"><?= e($field['value']) ?></td>
                            <?php endforeach; ?>
                            <td style="min-width:180px;">
                                <?php if ($index === 0): ?>
                                    <div class="inline">
                                        <?php foreach ($entry['keywords'] as $keyword): ?>
                                            <span class="badge"><?= e($keyword['keyword']) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (empty($entry['keywords'])): ?>
                                            <span class="muted">なし</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="detail-sections">
            <?php foreach ($entry['field_values'] as $field): ?>
                <section class="detail-section">
                    <h2 class="detail-section-title"><?= e($field['label']) ?></h2>
                    <div class="detail-section-value"><?= e($field['value']) ?></div>
                </section>
            <?php endforeach; ?>
            <section class="detail-section">
                <h2 class="detail-section-title">キーワード</h2>
                <div class="inline">
                    <?php foreach ($entry['keywords'] as $keyword): ?>
                        <span class="badge"><?= e($keyword['keyword']) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($entry['keywords'])): ?>
                        <span class="muted">キーワードはありません。</span>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>
</div>
<div class="card">
    <div class="detail-footer">
        <a class="btn btn-light" href="/dictionary">一覧へ戻る</a>
        <form method="post" action="/dictionary/delete" onsubmit="return confirm('この辞書を削除しますか？');" style="margin:0;">
            <?= csrf_field() ?>
            <input type="hidden" name="entry_id" value="<?= e($entry['entry_id']) ?>">
            <button class="btn btn-danger" type="submit">削除</button>
        </form>
    </div>
</div>
